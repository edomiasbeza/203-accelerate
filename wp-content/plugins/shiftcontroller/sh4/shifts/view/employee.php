<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_View_Employee
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Shifts_View_Common $common,
		SH4_Shifts_Query $shiftsQuery,

		SH4_Employees_Query $employees,
		SH4_App_Query $appQuery,

		SH4_Shifts_Availability $availability,
		SH4_Shifts_Conflicts $conflicts,
		SH4_Shifts_View_Zoom $viewZoom
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->layout = $layout;

		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );

		$this->employees = $hooks->wrap($employees);
		$this->appQuery = $hooks->wrap( $appQuery );

		$this->viewZoom = $hooks->wrap( $viewZoom );
		$this->common = $hooks->wrap( $common );

		$this->availability = $hooks->wrap( $availability );
		$this->conflicts = $hooks->wrap( $conflicts );
	}

	public function renderConfirm( $id, $employeeId )
	{
		$change = array();

		$employee = $this->employees->findById( $employeeId );
		$changeView = $employee->getTitle();

		$change['employee'] = $changeView;
		$to = 'shifts/' . $id . '/employee/' . $employeeId;

		$out = $this->viewZoom->render( $id, $change, $to );
		return $out;
	}

	public function render( $ids )
	{
		$ids = HC3_Functions::unglueArray( $ids );
		$strIds = (count($ids) < 2) ? $ids[0] : HC3_Functions::glueArray( $ids );

		$models = $this->shiftsQuery->findManyById( $ids );

		$currentEmployeesIds = array();
		$employeesIds = array();
		foreach( $models as $model ){
			$calendar = $model->getCalendar();
			$thisEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
			foreach( $thisEmployees as $eid => $emp ){
				if( ! in_array($eid, $employeesIds) ){
					$employeesIds[] = $eid;
				}
			}

			$thisEmployee = $model->getEmployee();
			$thisEmployeeId = $thisEmployee->getId();
			if( ! in_array($thisEmployeeId, $currentEmployeesIds) ){
				$currentEmployeesIds[] = $thisEmployeeId;
			}
		}

		$employees = array();
		if( $employeesIds ){
			$employees = $this->employees->findManyActiveById( $employeesIds );
		}

		unset( $employees[0] );
		if( count($currentEmployeesIds) < 2 ){
			foreach( $currentEmployeesIds as $currentEmployeeId ){
				unset( $employees[$currentEmployeeId] );
			}
		}

		$employeesView = array();
		$employeesWithConflictsView = array();

		foreach( $employees as $employee ){
			$eid = $employee->getId();

			$withConflicts = 0;
			$withAvailability = 0;
			$withNothing = 0;

			reset( $models );
			foreach( $models as $model ){
				$id = $model->getId();
				$calendar = $model->getCalendar();
				$calendarId = $calendar->getId();
				$start = $model->getStart();
				$end = $model->getEnd();

				$testModel = new SH4_Shifts_Model( $id, $calendar, $start, $end, $employee );

				$conflicts = $this->conflicts->get($testModel);
				if( $conflicts ){
					$withConflicts++;
				}
				else {
					if( (! $this->availability->hasAvailability()) OR $this->availability->get($testModel) ){
						$withAvailability++;
					}
					else {
						$withNothing++;
					}
				}
			}

			$thisView = array();
			$employeeView = $employee->getTitle();
			$employeeView = $this->ui->makeSpan( $employeeView )
				->tag('font-size', 4)
				->tag('font-style', 'bold')
				;

			$descriptionView = $employee->getDescription();
			if( strlen($descriptionView) ){
				$descriptionView = $this->ui->makeLongText( $descriptionView );
				$employeeView = $this->ui->makeList( array($employeeView, $descriptionView) )
					->gutter(1)
					;
			}

			$thisView[] = $employeeView;

			$conflictsView = array();

			if( $withAvailability ){
				$sign = ( count($ids) < 2 ) ? '&check;' : $withAvailability;
				$sign = $this->ui->makeBlock( $sign )
					->tag('padding', 'x1')
					->tag('color', 'white')
					->tag('bgcolor', 'olive')
					->addAttr( 'title', '__Available__' )
					;
				$conflictsView[] = $sign;
			}

			if( $withNothing ){
				$sign = ( count($ids) < 2 ) ? '&check;' : $withNothing;
				$sign = $this->ui->makeBlock( $sign )
					->tag('padding', 'x1')
					->tag('color', 'white')
					->tag('bgcolor', 'gray')
					->addAttr( 'title', '__No Availability__' )
					;
				$conflictsView[] = $sign;
			}

			if( $withConflicts ){
				$sign = (count($ids) < 2) ? '&nbsp;!&nbsp;' : $withConflicts;
				$sign = $this->ui->makeBlock( $sign )
					->tag('padding', 'x1')
					->tag('color', 'white')
					->tag('bgcolor', 'maroon')
					->addAttr( 'title', '__Conflicts__' )
					;

				if( count($ids) < 2 ){
					$shiftId = $ids[0];
					$conflictsLink = array('conflicts', $shiftId, $calendarId, $start, $end, $eid);
					$conflictsLink = join('/', $conflictsLink);

					$sign = $this->ui->makeAhref( $conflictsLink, $sign )
						->newWindow()
						;
				}

				$conflictsView[] = $sign;
			}

			$conflictsView = $this->ui->makeListInline( $conflictsView )
				->gutter(1)
				;
			$conflictsView = $this->ui->makeBlock($conflictsView)
				->addAttr('style', 'position: absolute; right:.2em; top:.2em; z-index: 100;')
				;

			$thisView[] = $conflictsView;

			$thisView = $this->ui->makeBlock( $this->ui->makeCollection($thisView) )
				->addAttr('style', 'position: relative;')
				;
			// $thisView = $this->ui->makeList( $thisView )->gutter(0);

			$btn = $this->ui->makeInputSubmit('__Select__')
				->tag('secondary')
				;
			if( $withConflicts ){
				$btn
					->tag('confirm')
					;
			}

			$to = array( 'shifts', $strIds, 'employee', $eid );
			$to = join('/', $to);

			$form = $this->ui->makeForm( $to, $btn );

			$thisView = $this->ui->makeList( array($thisView, $form) )->gutter(2);

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 2)
				;

			if( $withAvailability == count($ids) ){
				$thisView
					->tag('border-color', 'green')
					;
			}

			if( $withConflicts ){
				$employeesWithConflictsView[] = $thisView;
			}
			else {
				$employeesView[] = $thisView;
			}
		}

		if( $employeesView OR $employeesWithConflictsView ){

			$out = $this->ui->makeGrid();
			foreach( $employeesView as $ev ){
				$out->add( $ev, 3, 12 );
			}

			if( $employeesWithConflictsView ){
				$out = array( $out );

				$employeesWithConflictsView = $this->ui->makeGrid( $employeesWithConflictsView, 4 );
				$employeesWithConflictsView = $this->ui->makeCollapse( '__Unavailable Employees__', $employeesWithConflictsView );
				$out[] = $employeesWithConflictsView;

				$out = $this->ui->makeList( $out )->gutter(2);
			}
		}
		else {
			$out = array();
			$out[] = '__No Available Employees__';
			$out = $this->ui->makeList( $out );
		}

		$this->layout
			->setContent( $out )
			->setHeader( $this->self->header($model) )
			;

		if( count($ids) > 1 ){
			$breadcrumb = array();
			reset( $models );
			foreach( $models as $model ){
				$breadcrumb[] = $this->common->breadcrumb($model);
			}
			$breadcrumbMultiline = TRUE;
		}
		else {
			$breadcrumb = $this->common->breadcrumb($model);
			$breadcrumbMultiline = FALSE;
		}

		$this->layout
			->setBreadcrumb( $breadcrumb, $breadcrumbMultiline )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header( SH4_Shifts_Model $model )
	{
		$out = $model->isOpen() ? '__Assign Employee__' : '__Change Employee__';
		return $out;
	}
}