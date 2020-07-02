<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_View_Day
{
	protected $borderColor = 'gray';
	protected $allCombos = array();
	protected $allCombosShift = array();
	protected $allCombosTimeoff = array();

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,
		HC3_Enqueuer $enqueuer,
		HC3_Settings $settings,

		HC3_Uri $uri,

		SH4_New_Acl $newAcl,

		SH4_Schedule_Html_Widget_DayGrid $widgetDayGrid,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Shifts_View_Widget $widget,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Schedule_Html_View_Common $common,
		SH4_Shifts_View_Common $shiftsCommon
		)
	{
		$this->self = $hooks->wrap($this);
		$enqueuer->addScript('schedule', 'sh4/schedule/assets/js/calendar.js?hcver=' . SH4_VERSION);

		$this->ui = $ui;

		$this->uri = $uri;
		$this->t = $t;
		$this->request = $request;

		$this->settings = $hooks->wrap( $settings );
		$this->newAcl = $hooks->wrap( $newAcl );

		$this->widgetDayGrid = $hooks->wrap( $widgetDayGrid );
		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->widget = $hooks->wrap( $widget );
		$this->common = $hooks->wrap( $common );
		$this->shiftsCommon = $hooks->wrap( $shiftsCommon );

		$this->allCombos = $this->newAcl->findAllCombos();
		$this->allCombosShift = $this->newAcl->findAllCombosShift();
		$this->allCombosTimeoff = $this->newAcl->findAllCombosTimeoff();
	}

	public function render()
	{
		$shifts = $this->common->getShifts();
		$params = $this->request->getParams();
		$today = $this->t->setNow()->formatDateDb();

		$startDate = $params['start'];
		$endDate = $this->t->setDateDb( $startDate )->modify('+6 days')->formatDateDb();

		$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
	// $dayEnd = $this->t->setDateDb( $startDate )->modify('+1 day')->formatDateTimeDb();
		$dayEnd = $this->t->setDateDb( $startDate )->modify('+7 days')->formatDateTimeDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );
		$this->t->setDateDb( $startDate );
		$dates = array();
		$rexDate = $startDate;
		while( $rexDate <= $endDate ){
			if( $disabledWeekdays ){
				$wkd = $this->t->getWeekday();
				if( in_array($wkd, $disabledWeekdays) ){
					$rexDate = $this->t->modify('+1 day')->formatDateDb();
					continue;
				}
			}
			$dates[] = $rexDate;
			$rexDate = $this->t->modify('+1 day')->formatDateDb();
		}

		$endDate = $startDate;

		$viewShifts = array();
		$iknow = array('date');

		$this->shiftsDuration->reset();

		// foreach( $shifts as $shift ){
		// 	$this->shiftsDuration->add( $shift );
		// 	$start = $shift->getStart();
		// 	$end = $shift->getEnd();

		// 	$startDayTime = substr($start, 8, 4);
		// 	$endDayTime = substr($end, 8, 4);
		// 	$shiftAllDay = ( (0 == $startDayTime) && (0 == $endDayTime) ) ? TRUE : FALSE;

		// 	$this->t->setDateTimeDb( $end )->modify('-1 second');
		// 	$shiftEndDate = $this->t->formatDateDb();

		// 	$this->t->setDateTimeDb( $start );
		// 	$shiftStartDate = $this->t->formatDateDb();
		// }

		reset( $shifts );
		foreach( $shifts as $shift ){
			$this->shiftsDuration->add( $shift );
			$start = $shift->getStart();
			$end = $shift->getEnd();

			$startDayTime = substr($start, 8, 4);
			$endDayTime = substr($end, 8, 4);
			$shiftAllDay = ( (0 == $startDayTime) && (0 == $endDayTime) ) ? TRUE : FALSE;

			$this->t->setDateTimeDb( $end )->modify('-1 second');
			$shiftEndDate = $this->t->formatDateDb();

			$this->t->setDateTimeDb( $start );
			$shiftStartDate = $this->t->formatDateDb();

			$rexDate = $shiftStartDate;
			while( $rexDate <= $shiftEndDate ){
				if( in_array($rexDate, $dates) ){
					if( ! array_key_exists($rexDate, $viewShifts) ){
						$viewShifts[$rexDate] = array();
					}
					$viewShifts[$rexDate][] = $shift;
				}

				if( ! $shiftAllDay ){
					break;
				}
				$this->t->modify( '+1 day' );
				$rexDate = $this->t->formatDateDb();
			}
		}

		$iknow = array('date');
		$rows = array();

		foreach( $dates as $date ){
			$row = array();
			$cell = array();

			$this->t->setDateDb( $date );
			$dateView = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();
			$cell[] = $dateView; 

			$thisShifts = isset($viewShifts[$date]) ? $viewShifts[$date] : array();

			if( $thisShifts ){
				$gridView = $this->self->renderDay( $thisShifts, $iknow, $date );
				$cell[] = $gridView;
			}

			$toParams = array(
				'date'		=> $date
				);
			if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
				if( ! in_array(-1, $params['employee']) ){
					$toParams['employee'] = $params['employee'][0];
				}
			}

			$links = array();

			if( $this->allCombosShift ){
				$label = '+' . ' ' . '__Shift__';
				$thisTo = 'new/shift';
				$thisTo = array( $thisTo, $toParams );
				$link = $this->ui->makeAhref( $thisTo, $label )
					->tag('tab-link')
					->tag('align', 'center')
					// ->addAttr('title', '__Add New__')
					;
				if( $today > $date ){
					$link->tag('muted', 3);
				}
				$links[] = $link;
			}

			if( $this->allCombosTimeoff ){
				$label = '+' . ' ' . '__Time Off__';
				$thisTo = 'new/timeoff';
				$thisTo = array( $thisTo, $toParams );
				$link = $this->ui->makeAhref( $thisTo, $label )
					->tag('tab-link')
					->tag('align', 'center')
					// ->addAttr('title', '__Add New__')
					;
				if( $today > $date ){
					$link->tag('muted', 3);
				}
				$links[] = $link;
			}

			$links = $this->ui->makeList( $links )->gutter(0);

			$cell[] = $links;

			$cell = $this->ui->makeList( $cell )
				->gutter(1)
				->tag('margin', 1)
				;

			$row[] = $cell;

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( NULL, $rows, FALSE )
			->gutter(0)
			// ->setBordered( $this->borderColor )
			;

		return $out;
	}

	public function renderByCalendar()
	{
		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();

		$today = $this->t->setNow()->formatDateDb();
		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();
		$startDate = $params['start'];
		$endDate = $this->t->setDateDb( $startDate )->modify('+6 days')->formatDateDb();

		$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
	// $dayEnd = $this->t->setDateDb( $startDate )->modify('+1 day')->formatDateTimeDb();
		$dayEnd = $this->t->setDateDb( $startDate )->modify('+7 days')->formatDateTimeDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );
		$this->t->setDateDb( $startDate );
		$dates = array();
		$rexDate = $startDate;
		while( $rexDate <= $endDate ){
			if( $disabledWeekdays ){
				$wkd = $this->t->getWeekday();
				if( in_array($wkd, $disabledWeekdays) ){
					$rexDate = $this->t->modify('+1 day')->formatDateDb();
					continue;
				}
			}
			$dates[] = $rexDate;
			$rexDate = $this->t->modify('+1 day')->formatDateDb();
		}

		$viewCalendars = array();
		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			// $calendarView = $this->calendarsPresenter->presentTitle( $calendar );

			$label = $calendar->getTitle();

			$title = htmlspecialchars( $label );
			$description = $calendar->getDescription();
			if( strlen($description) ){
				$description = htmlspecialchars( $description );
				$title .= "\n" . $description;
			}

			$label = $this->ui->makeSpan( $label )
				->addAttr( 'title', $title )
				;

			$viewCalendars[ $calendarId ] = $label;
		}

		$viewShifts = array();

		$iknow = array('calendar', 'date');
		$hori = FALSE;

		$allEmployees = $this->common->findAllEmployees();
		if( count($allEmployees) < 2 ){
			$iknow[] = 'employee';
		}

		$out = array();

		reset( $dates );
		foreach( $dates as $date ){
			$thisOut = array();
			$rows = array();

			$this->t->setDateDb( $date );
			$dateView = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();
			$thisOut[] = $dateView; 

			$viewShifts = array();

			reset( $shifts );
			foreach( $shifts as $shift ){
				$start = $shift->getStart();
				$end = $shift->getEnd();

				$startDate = $this->t->setDateTimeDb( $start )->formatDateDb();
				// echo "START '$startDate' VS '$date'<br>";
				if( $startDate > $date ){
					continue;
				}

				$endDate = $this->t->setDateTimeDb( $end )->formatDateDb();
				// echo "END '$endDate' VS '$date'<br>";
				if( $endDate < $date ){
					continue;
				}

				// echo "'$startDate' - '$endDate' VS '$date' OK<br><br>";

				$calendar = $shift->getCalendar();
				$id = $calendar->getId();

				if( ! array_key_exists($id, $viewShifts) ){
					$viewShifts[$id] = array();
				}

				$viewShifts[$id][] = $shift;
			}

			$header = array();
			$header[] = NULL;
			$header[] = NULL;

			$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
			$dayEnd = $this->t->setDateDb( $startDate )->modify('+1 day')->formatDateTimeDb();

			reset( $viewCalendars );
			foreach( $viewCalendars as $id => $calendarView ){
				$row = array();

				$this->shiftsDuration->reset();
				if( isset($viewShifts[$id]) ){
					foreach( $viewShifts[$id] as $shift ){
						$this->shiftsDuration->add( $shift );
					}

					$outReport = $this->common->renderReport( $this->shiftsDuration, FALSE );
					$outReport = $this->ui->makeSpan( $outReport )
						->tag('font-size', 2)
						;
					$calendarView = $this->ui->makeList( array($calendarView, $outReport) )
						->gutter(1)
						;
				}

				$calendarView = $this->ui->makeBlock( $calendarView )
					->tag('padding', 2)
					->tag('nowrap')
					;
				$row[] = $calendarView;

				$cell = array();

				if( isset($viewShifts[$id]) ){
					$gridView = $this->self->renderDay( $viewShifts[$id], $iknow, $date );
					$cell[] = $gridView;
				}

				$thisCalendar = $calendars[$id];
				if( $thisCalendar->isTimeoff() ){
					$label = '+ ' . '__Time Off__';
				}
				else {
					$label = '+ ' . '__Shift__';
				}

				$to = 'new';
				$toParams = array(
					'date'		=> $date
					);
				if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
					if( ! in_array(-1, $params['employee']) ){
						$toParams['employee'] = $params['employee'][0];
					}
				}
				$to = array( $to, $toParams );

				$addOk = FALSE;
				if( isset($toParams['employee']) ){
					$testComboId = $id . '-' . $toParams['employee'];
					if( isset($this->allCombos[$testComboId]) ){
						$addOk = TRUE;
					}
				}
				else {
					$testComboId = $id . '-';
					reset( $this->allCombos );
					foreach( $this->allCombos as $comboId ){
						if( $testComboId == substr($comboId, 0, strlen($testComboId)) ){
							$addOk = TRUE;
							break;
						}
					}
				}

				if( $addOk ){
					$link = $this->ui->makeAhref( $to, $label )
						->tag('tab-link')
						->tag('align', 'center')
						;

					if( $today > $date ){
						$link->tag('muted', 3);
					}

					$cell[] = $link;
				}

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', 1)
					;

				$row[] = $cell;

				$rows[] = $row;
			}

			$thisOut[] = $this->ui->makeTable( $header, $rows, FALSE )
				->gutter(0)
				->setBordered( $this->borderColor )
				// ->setSegments( $weekSegments )
				->setLabelled( TRUE )
				;

			$thisOut = $this->ui->makeList( $thisOut );

			$out[] = $thisOut;
		}

		$out = $this->ui->makeList( $out );

		return $out;
	}

	public function renderByEmployee()
	{
		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();
		$today = $this->t->setNow()->formatDateDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();
		$startDate = $params['start'];
		$endDate = $this->t->setDateDb( $startDate )->modify('+6 days')->formatDateDb();

		$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
	// $dayEnd = $this->t->setDateDb( $startDate )->modify('+1 day')->formatDateTimeDb();
		$dayEnd = $this->t->setDateDb( $startDate )->modify('+7 days')->formatDateTimeDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );
		$this->t->setDateDb( $startDate );
		$dates = array();
		$rexDate = $startDate;
		while( $rexDate <= $endDate ){
			if( $disabledWeekdays ){
				$wkd = $this->t->getWeekday();
				if( in_array($wkd, $disabledWeekdays) ){
					$rexDate = $this->t->modify('+1 day')->formatDateDb();
					continue;
				}
			}
			$dates[] = $rexDate;
			$rexDate = $this->t->modify('+1 day')->formatDateDb();
		}

		$viewEmployees = array();
		foreach( $employees as $employee ){
			$label = $employee->getTitle();

			$title = htmlspecialchars( $label );
			$description = $employee->getDescription();
			if( strlen($description) ){
				$description = htmlspecialchars( $description );
				$title .= "\n" . $description;
			}

			$label = $this->ui->makeSpan( $label )
				->tag( 'font-size', 4 )
				->addAttr( 'title', $title )
				;

			$viewEmployees[ $employee->getId() ] = $label;
		}

		$viewShifts = array();

		$iknow = array('employee', 'date');
		$hori = FALSE;

		$out = array();

		reset( $dates );
		foreach( $dates as $date ){
			$thisOut = array();

			$thisOut = array();
			$rows = array();

			$this->t->setDateDb( $date );
			$dateView = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();
			$thisOut[] = $dateView; 

			$viewShifts = array();

			foreach( $shifts as $shift ){
				$employee = $shift->getEmployee();
				$id = $employee ? $employee->getId() : 0;

				if( ! array_key_exists($id, $viewShifts) ){
					$viewShifts[$id] = array();
				}

				$viewShifts[$id][] = $shift;
			}

			$header = array();
			$header[] = NULL;
			$header[] = NULL;

			$rows = array();

			foreach( $viewEmployees as $id => $employeeView ){
				$row = array();

				$this->shiftsDuration->reset();
				if( isset($viewShifts[$id]) ){
					$counted = 0;
					foreach( $viewShifts[$id] as $shift ){
						$shiftCalendar = $shift->getCalendar();
						// if( ! $shiftCalendar->isShift() ){
							// continue;
						// }
						$this->shiftsDuration->add( $shift );
						$counted++;
					}

					if( $counted ){
						$outReport = $this->common->renderReport( $this->shiftsDuration, FALSE );
						$outReport = $this->ui->makeSpan( $outReport )
							->tag('font-size', 2)
							;
						$employeeView = $this->ui->makeList( array($employeeView, $outReport) )
							->gutter(1)
							;
					}
				}

				$employeeView = $this->ui->makeBlock( $employeeView )
					->tag('padding', 2)
					->tag('nowrap')
					;
				$row[] = $employeeView;

				$cell = array();

				if( isset($viewShifts[$id]) ){
					$gridView = $this->self->renderDay( $viewShifts[$id], $iknow, $date );
					$cell[] = $gridView;
				}

				$toParams = array(
					'employee'	=> $id,
					'date'		=> $date
					);

				$links = array();

				$comboId = 0 . '-' . $id;

				if( isset($this->allCombosShift[$comboId]) ){
					$label = '+' . ' ' . '__Shift__';
					$thisTo = 'new/shift';
					$thisTo = array( $thisTo, $toParams );
					$link = $this->ui->makeAhref( $thisTo, $label )
						->tag('tab-link')
						->tag('align', 'center')
						// ->addAttr('title', '__Add New__')
						;
					if( $today > $date ){
						$link->tag('muted', 3);
					}
					$links[] = $link;
				}

				if( isset($this->allCombosTimeoff[$comboId]) ){
					$label = '+' . ' ' . '__Time Off__';
					$thisTo = 'new/timeoff';
					$thisTo = array( $thisTo, $toParams );
					$link = $this->ui->makeAhref( $thisTo, $label )
						->tag('tab-link')
						->tag('align', 'center')
						// ->addAttr('title', '__Add New__')
						;
					if( $today > $date ){
						$link->tag('muted', 3);
					}
					$links[] = $link;
				}

				$links = $this->ui->makeList( $links )->gutter(0);

				$cell[] = $links;

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', 1)
					;

				$row[] = $cell;

				$rows[] = $row;
			}


			$thisOut[] = $this->ui->makeTable( $header, $rows, FALSE )
				->gutter(0)
				->setBordered( $this->borderColor )
				// ->setSegments( $weekSegments )
				->setLabelled( TRUE )
				;

			$thisOut = $this->ui->makeList( $thisOut );

			$out[] = $thisOut;
		}

		$out = $this->ui->makeList( $out );

		return $out;
	}

	public function renderDay( $shifts, $iknow, $startDate = NULL )
	{
		$hori = FALSE;
		$params = $this->request->getParams();

		$hideui = $params['hideui'];
		$noZoom = in_array('shiftdetails', $hideui);

		if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
			if( ! in_array(-1, $params['employee']) ){
				$iknow[] = 'employee';
			}
		}
		if( array_key_exists('calendar', $params) && (count($params['calendar']) == 1) ){
			$iknow[] = 'calendar';
		}

		if( NULL === $startDate ){
			$startDate = $params['start'];
		}

		$this->t->setDateDb( $startDate );
		$minTime = $this->settings->get('datetime_min_time');
		if( $minTime ){
			$this->t->modify('+' . $minTime . ' seconds');
		}
		$dayStart = $this->t->formatDateTimeDb();

		$this->t->setDateDb( $startDate );
		$maxTime = $this->settings->get('datetime_max_time');
		if( $maxTime ){
			$this->t->modify('+' . $maxTime . ' seconds');
		}
		else {
			$this->t->modify('+1 day');
		}
		$dayEnd = $this->t->formatDateTimeDb();

		$grid = $this->widgetDayGrid
			->reset()
			;
		$grid->setRange( $dayStart, $dayEnd );

	// groups?
		$groups = array();
		$groupedShifts = array();
		reset( $shifts );
		foreach( $shifts as $shift ){
			if( ! $shift->isOpen() ){
				continue;
			}

			$groupId = $shift->getGroupingId();

			if( ! isset($groups[$groupId]) ){
				$groups[ $groupId ] = $shift->getId();
				$groupedShifts[ $shift->getId() ] = array( $shift->getId() );
			}
			else {
				$mainShiftId = $groups[ $groupId ];
				$groupedShifts[ $mainShiftId ][] = $shift->getId();
				$groupedShifts[ $shift->getId() ] = 0;
			}
		}

		reset( $shifts );
		foreach( $shifts as $shift ){
			$id = $shift->getId();
			if( isset($groupedShifts[$id]) && (! $groupedShifts[$id]) ){
				continue;
			}

			$groupedQty = NULL;
			if( isset($groupedShifts[$id]) && (count($groupedShifts[$id]) > 1) ){
				$groupedQty = count($groupedShifts[$id]);
			}

			$thisView = $this->widget->render( $shift, $iknow, $hori, $noZoom, $groupedQty );
			$shiftId = $shift->getId();

			$menu = array();
			$fullMenu = $this->shiftsCommon->menu( $shift );
			foreach( $fullMenu as $k0 => $thisMenu ){
				foreach( $thisMenu as $k1 => $actionArray ){
					$k = $k0 . '_' . $k1;
					$menu[$k] = $actionArray;
				}
			}
			if( $menu ){
				$menu = $this->ui->helperActionsFromArray( $menu, TRUE );

				$menu = $this->ui->makeCollection( $menu );

				$checkbox = $this->ui->makeInputCheckbox( 'id[]', NULL, $shiftId, TRUE );
				$checkbox = $this->ui->makeBlock( $checkbox )
					->addAttr('class', 'sh4-shift-checker')
					->addAttr('style', 'display: none;')
					;
				$thisView = $this->ui->makeCollection( array($thisView, $checkbox, $menu) );
			}

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('block')
				->addAttr('class', 'sh4-shift-widget')
				->addAttr('style', 'padding-right: 1px;')
				;

			$grid
				->add( $shift->getStart(), $shift->getEnd(), $thisView )
				;
		}

		$return = $grid->render();
		return $return;
	}
}