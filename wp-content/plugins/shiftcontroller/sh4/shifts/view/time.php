<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_View_Time
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Time $t,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Shifts_View_Common $common,
		SH4_Shifts_Query $shiftsQuery,

		SH4_Employees_Query $employees,
		SH4_App_Query $appQuery,

		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->ui = $ui;
		$this->t = $t;
		$this->layout = $layout;

		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );

		$this->employees = $hooks->wrap($employees);
		$this->appQuery = $hooks->wrap( $appQuery );

		$this->common = $hooks->wrap($common);
		$this->self = $hooks->wrap($this);
		$this->conflicts = $hooks->wrap($conflicts);
	}

	public function render( $id )
	{
		$model = $this->shiftsQuery->findById( $id );

		$time = array( $model->getStartInDay(), $model->getEndInDay() );

		$breakOn = FALSE;
		$breakStart = $model->getBreakStartInDay();
		$breakEnd = $model->getBreakEndInDay();

		if( ! ((NULL === $breakStart) && (NULL === $breakEnd)) ){
			$breakOn = TRUE;
			$breakInput = $this->ui->makeInputTimeRange( 'break', NULL, array($breakStart, $breakEnd) );
		}
		else {
			$breakInput = $this->ui->makeInputTimeRange( 'break', NULL );
		}

		$breakInput = $this->ui->makeCollapseCheckbox(
			'break_on',
			'__Lunch Break__' . '?',
			$breakInput
			);
		if( $breakOn ){
			$breakInput->expand();
		}

		$inputs = $this->ui->makeList()
			->add( $this->ui->makeInputTimeRange( 'time', '__Time__', $time ) )
			->add( $breakInput )
			;

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;

		$form = $this->ui->makeList()
			->add( $inputs )
			->add( $buttons )
			;

		$out = $this->ui->makeForm(
			'shifts/' . $id . '/time',
			$form
			);

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->common->breadcrumb($model) )
			->setHeader( '__Change Time__' )
			;
		$out = $this->layout->render();

		return $out;
	}

	public function renderStart( $id )
	{
		$model = $this->shiftsQuery->findById( $id );
		$shiftId = $id;

		$calendar = $model->getCalendar();
		$calendarId = $calendar->getId();
		$employee = $model->getEmployee();
		$employeeId = $employee->getId();

		$currentStart = $model->getStart();
		$currentEnd = $model->getEnd();

		$options = array();
		$this->t->setDateTimeDb( $currentStart )->setStartDay();
		$option = $this->t->formatDateTimeDb();
		while( $option < $currentEnd ){
			$options[] = $option;
			$this->t->modify( '+' . (5 * 60) . ' seconds');
			$option = $this->t->formatDateTimeDb();
		}

		$allsView = array();

		foreach( $options as $option ){
			$testModel = new SH4_Shifts_Model( $id, $calendar, $option, $currentEnd, $employee );
			$conflicts = $this->conflicts->get($testModel);

			$this->t->setDateTimeDb( $option );

			$thisView = $this->t->formatTime();
			$thisView = $this->ui->makeSpan( $thisView )
				->tag('font-size', 4)
				->tag('font-style', 'bold')
				;

			if( $conflicts ){
				$conflictsView = '__Conflicts__';

				$conflictsView = $this->ui->makeBlock('!')
					->tag('align', 'center')
					->addAttr('style', 'width: 1em;')
					->tag('border')
					->tag('border-color', 'red' )
					->tag('bgcolor', 'lightred')
					->tag('muted', 1)
					->addAttr('title', '__Conflicts__')
					->addAttr('style', 'position: absolute; right:0; top:0; z-index: 100;')
					;

				$conflictsLink = array('conflicts', $shiftId, $calendarId, $option, $currentEnd, $employeeId);
				$conflictsLink = join('/', $conflictsLink);

				$conflictsView = $this->ui->makeAhref( $conflictsLink, $conflictsView )
					->newWindow()
					->tag('color', 'red')
					;

				$thisView = $this->ui->makeBlock( $this->ui->makeCollection( array($thisView, $conflictsView) ) )
					->addAttr('style', 'position: relative;')
					;
			}

			if( $option === $currentStart ){
				$btn = $this->ui->makeInputSubmit('__Current Time__')->tag('secondary');
			}
			else {
				$btn = $this->ui->makeInputSubmit('__Select__')->tag('secondary');
				if( $conflicts ){
					$btn->tag('confirm');
				}
			}

			$form = $this->ui->makeForm(
				'shifts/' . $id . '/time/' . $option . '/' . $currentEnd,
				$btn
				);

			$thisView = $this->ui->makeList( array($thisView, $form) )->gutter(2);

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 2)
				;

			if( $conflicts ){
			}
			else {
				$thisView
					->tag('border-color', 'green')
					;
			}

			if( $option === $currentStart ){
				$thisView
					->tag('bgcolor', 'lightgreen')
					;
			}

			$allsView[] = $thisView;
		}

		if( $allsView ){
			$out = $this->ui->makeGrid();
			foreach( $allsView as $ev ){
				$out->add( $ev, 2, 6 );
			}
		}
		else {
			$out = array();
			$out[] = '__No Available Time__';
			$out = $this->ui->makeList( $out );
		}

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->common->breadcrumb($model) )
			->setHeader( '__Start Time__' )
			;
		$out = $this->layout->render();

		return $out;
	}

	public function renderEnd( $id )
	{
		$model = $this->shiftsQuery->findById( $id );
		$shiftId = $id;

		$calendar = $model->getCalendar();
		$calendarId = $calendar->getId();
		$employee = $model->getEmployee();
		$employeeId = $employee->getId();

		$currentStart = $model->getStart();
		$currentEnd = $model->getEnd();

		$this->t->setDateTimeDb( $currentStart );
		$maxEnd = $this->t->modify('+24 hours')->formatDateTimeDb();

		$options = array();
		$this->t->setDateTimeDb( $currentStart );
		$option = $this->t->formatDateTimeDb();
		while( $option <= $maxEnd ){
			if( $option > $currentStart ){
				$options[] = $option;
			}
			$this->t->modify( '+' . (5 * 60) . ' seconds');
			$option = $this->t->formatDateTimeDb();
		}

		$allsView = array();

		foreach( $options as $option ){
			$testModel = new SH4_Shifts_Model( $id, $calendar, $currentStart, $option, $employee );
			$conflicts = $this->conflicts->get($testModel);

			$this->t->setDateTimeDb( $option );

			$thisView = $this->t->formatTime();
			$thisView = $this->ui->makeSpan( $thisView )
				->tag('font-size', 4)
				->tag('font-style', 'bold')
				;

			if( $conflicts ){
				$conflictsView = '__Conflicts__';

				$conflictsView = $this->ui->makeBlock('!')
					->tag('align', 'center')
					->addAttr('style', 'width: 1em;')
					->tag('border')
					->tag('border-color', 'red' )
					->tag('bgcolor', 'lightred')
					->tag('muted', 1)
					->addAttr('title', '__Conflicts__')
					->addAttr('style', 'position: absolute; right:0; top:0; z-index: 100;')
					;

				$conflictsLink = array('conflicts', $shiftId, $calendarId, $currentStart, $option, $employeeId);
				$conflictsLink = join('/', $conflictsLink);

				$conflictsView = $this->ui->makeAhref( $conflictsLink, $conflictsView )
					->newWindow()
					->tag('color', 'red')
					;

				$thisView = $this->ui->makeBlock( $this->ui->makeCollection( array($thisView, $conflictsView) ) )
					->addAttr('style', 'position: relative;')
					;
			}

			if( $option === $currentEnd ){
				$btn = $this->ui->makeInputSubmit('__Current Time__')->tag('secondary');
			}
			else {
				$btn = $this->ui->makeInputSubmit('__Select__')->tag('secondary');
				if( $conflicts ){
					$btn->tag('confirm');
				}
			}

			$form = $this->ui->makeForm(
				'shifts/' . $id . '/time/' . $currentStart . '/' . $option,
				$btn
				);

			$thisView = $this->ui->makeList( array($thisView, $form) )->gutter(2);

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 2)
				;

			if( $conflicts ){
			}
			else {
				$thisView
					->tag('border-color', 'green')
					;
			}

			if( $option === $currentEnd ){
				$thisView
					->tag('bgcolor', 'lightgreen')
					;
			}

			$allsView[] = $thisView;
		}

		if( $allsView ){
			$out = $this->ui->makeGrid();
			foreach( $allsView as $ev ){
				$out->add( $ev, 2, 6 );
			}
		}
		else {
			$out = array();
			$out[] = '__No Available Time__';
			$out = $this->ui->makeList( $out );
		}

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->common->breadcrumb($model) )
			->setHeader( '__End Time__' )
			;
		$out = $this->layout->render();

		return $out;
	}
}