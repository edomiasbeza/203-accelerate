<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_View_ControlDates
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,

		HC3_Enqueuer $enqueuer
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->ui = $ui;
		$this->request = $request;
		$this->t = $t;

		if( ! $request->isPrintView() ){
			$enqueuer
				->addScript('datepicker',	'hc3/ui/element/input/datepicker/assets/input.js')
				->addStyle('datepicker',	'hc3/ui/element/input/datepicker/assets/input.css')
				;
		}
	}

	public function render()
	{
		$out = NULL;

		$params = $this->request->getParams();
		$type = $params['type'];

		switch( $type ){
			case 'month':
				$out = $this->self->renderMonth();
				break;
			case '4weeks':
				$out = $this->self->renderFourWeeks();
				break;
			case 'week':
				$out = $this->self->renderWeek();
				break;
			case 'day':
				$out = $this->self->renderDay();
				break;
			default:
				$out = $this->self->renderCustom();
				break;
		}

		return $out;
	}

	public function renderCustom()
	{
		$slug = $this->request->getSlug();
		$params = $this->request->getParams();
		if( isset($params['time']) && ('now' == $params['time']) ){
			$params['time'] = $this->t->setNow()->formatDateTimeDb();
		}

	// date selection
		$timeInDay = 0;
		$exactStartDate = NULL;
		if( array_key_exists('time', $params) ){
			$time = $params['time'];
			$startDate = $this->t->setDateTimeDb( $params['time'] )->formatDateDb();
			$timeInDay = $this->t->getTimeInDay();
			$exactStartDate = $startDate;
		}
		elseif( array_key_exists('start', $params) ){
			$startDate = $params['start'];
		}
		else {
			$startDate = $this->t->setNow()->setStartDay()->formatDateDb();
		}

		if( ! $timeInDay ){
			$timeInDay = $this->t->setNow()->getTimeInDay();
			$exactStartDate = $this->t->setNow()->formatDateDb();
		}

		if( array_key_exists('time', $params) ){
			$endDate = $startDate;
		}
		elseif( array_key_exists('end', $params) ){
			$endDate = $params['end'];
		}
		else {
			$endDate = $this->t->modify('+1 week')->modify('-1 day')->formatDateDb();
		}

		if( FALSE !== strpos($endDate, '+') ){
			$this->t->setDateDb($startDate)->modify($endDate);
			$endDate = $this->t->formatDateDb();
		}

		if( isset($params['time']) ){
			$this->t->setDateTimeDb( $params['time'] );
			$label = $this->t->formatDateWithWeekday() . ' ' . $this->t->formatTime();
		}
		else {
			$label = $this->t->formatDateRange( $startDate, $endDate );
		}

		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 5)
			;

		$quickJumpForm = $this->ui->makeForm(
			'schedule/dates',
			$this->ui->makeListInline()
				->add( $this->ui->makeInputDatepicker( 'start', NULL, $startDate ) )
				->add( '-' )
				->add( $this->ui->makeInputDatepicker( 'end', NULL, $endDate ) )
				->add( $this->ui->makeInputSubmit( '&rarr;')->tag('primary') )
			);

		$exactTimeForm = $this->ui->makeForm(
			'schedule/exacttime',
			$this->ui->makeListInline()
				->add( $this->ui->makeInputDatepicker( 'date', NULL, $exactStartDate ) )
				->add( $this->ui->makeInputTime( 'time', NULL, $timeInDay ) )
				->add( $this->ui->makeInputSubmit( '&rarr;')->tag('primary') )
			);

		$quickJumpForm = $this->ui->makeList( array('__Date Range__', $quickJumpForm, '__Exact Time__', $exactTimeForm) );

		$quickJumpForm = $this->ui->makeCollapse( $label, $quickJumpForm );
		$out = $quickJumpForm;

		return $out;
	}

	public function renderWeek()
	{
		$slug = $this->request->getSlug();

		$params = $this->request->getParams('withoutDefault');
		$params['end'] = NULL;

		$startDate = $params['start'];

		$this->t->setDateDb($startDate)->setStartWeek();
		$next = $this->t->modify('+1 week')->formatDateDb();
		$prev = $this->t->modify('-2 weeks')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label
		$this->t->setDateDb( $startDate );
		$this->t->modify('+1 week')->modify('-1 day');
		$endDate = $this->t->formatDateDb();
		$label = $this->t->formatDateRange( $startDate, $endDate );

		$weekNo = $this->t->getWeekNo();
		$label .= ' [' . '__Week__' . ' #' . $weekNo . ']';

		$quickJumpForm = $this->ui->makeForm(
			'schedule/dates',
			$this->ui->makeListInline()
				->add( $this->ui->makeInputDatepicker( 'start', NULL, $startDate ) )
				->add(
					$this->ui->makeInputSubmit( '&rarr;' )
						->tag('primary')
						->tag('block')
					)
				->gutter(1)
			);

		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 5)
			;

		$quickJumpForm = $this->ui->makeCollapse( $label, $quickJumpForm );

		$controls = $this->ui->makeGrid( array($prevLink, $nextLink) );

		$out = $this->ui->makeGrid()
			->add( $quickJumpForm, 8, 12 )
			->add( $controls, 4, 12 )
			;

		return $out;
	}

	public function renderDay()
	{
		$slug = $this->request->getSlug();
		$params = $this->request->getParams('withoutDefault');

		$params['end'] = NULL;

		$startDate = $params['start'];

		$this->t->setDateDb($startDate);
		// $next = $this->t->modify('+1 day')->formatDateDb();
		// $prev = $this->t->modify('-2 days')->formatDateDb();
		$next = $this->t->modify('+7 day')->formatDateDb();
		$prev = $this->t->modify('-14 days')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label
		// $this->t->setDateDb( $startDate );
		// $label = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();

		$this->t->setDateDb( $startDate );
		$this->t->modify('+7 days')->modify('-1 day');
		$endDate = $this->t->formatDateDb();
		$label = $this->t->formatDateRange( $startDate, $endDate );

		$quickJumpForm = $this->ui->makeForm(
			'schedule/dates',
			$this->ui->makeListInline()
				->add( $this->ui->makeInputDatepicker( 'start', NULL, $startDate ) )
				->add(
					$this->ui->makeInputSubmit( '&rarr;' )
						->tag('primary')
						->tag('block')
					)
				->gutter(1)
			);

		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 5)
			;

		$quickJumpForm = $this->ui->makeCollapse( $label, $quickJumpForm );

		$controls = $this->ui->makeGrid( array($prevLink, $nextLink) );

		$out = $this->ui->makeGrid()
			->add( $quickJumpForm, 8, 12 )
			->add( $controls, 4, 12 )
			;

		return $out;
	}

	public function renderMonth()
	{
		$slug = $this->request->getSlug();
		$params = $this->request->getParams('withoutDefault');

		$params['end'] = NULL;

		$startDate = $params['start'];

		$this->t->setDateDb($startDate)->setStartMonth();
		$next = $this->t->modify('+1 month')->formatDateDb();
		$prev = $this->t->modify('-2 months')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label

		$this->t->setDateDb( $startDate );
		$label = array();
		$label[] = $this->t->getMonthName();
		$label[] = $this->t->getYear();
		$label = join(' ', $label);

		$quickJumpForm = $this->ui->makeForm(
			'schedule/dates',
			$this->ui->makeListInline()
				->add( $this->ui->makeInputDatepicker( 'start', NULL, $startDate ) )
				->add(
					$this->ui->makeInputSubmit( '&rarr;' )
						->tag('primary')
						->tag('block')
					)
				->gutter(1)
			);

		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 5)
			;

		$quickJumpForm = $this->ui->makeCollapse( $label, $quickJumpForm );

		$controls = $this->ui->makeGrid( array($prevLink, $nextLink) );

		$out = $this->ui->makeGrid()
			->add( $quickJumpForm, 8, 12 )
			->add( $controls, 4, 12 )
			;

		return $out;
	}

	public function renderFourWeeks()
	{
		$slug = $this->request->getSlug();
		$params = $this->request->getParams('withoutDefault');

		$params['end'] = NULL;

		$startDate = $params['start'];
		$endDate = $this->t->setDateDb($startDate)->setStartWeek()->modify('+4 weeks')->modify('-1 day')->formatDateDb();

		$this->t->setDateDb($startDate)->setStartWeek();
		$next = $this->t->modify('+4 weeks')->formatDateDb();
		$prev = $this->t->modify('-8 weeks')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label
		$this->t->setDateDb( $startDate );
		$label = $this->t->formatDateRange( $startDate, $endDate );

		$quickJumpForm = $this->ui->makeForm(
			'schedule/dates',
			$this->ui->makeListInline()
				->add( $this->ui->makeInputDatepicker( 'start', NULL, $startDate ) )
				->add(
					$this->ui->makeInputSubmit( '&rarr;' )
						->tag('primary')
						->tag('block')
					)
				->gutter(1)
			);

		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 5)
			;

		$quickJumpForm = $this->ui->makeCollapse( $label, $quickJumpForm );

		$controls = $this->ui->makeGrid( array($prevLink, $nextLink) );

		$out = $this->ui->makeGrid()
			->add( $quickJumpForm, 8, 12 )
			->add( $controls, 4, 12 )
			;

		return $out;
	}

}