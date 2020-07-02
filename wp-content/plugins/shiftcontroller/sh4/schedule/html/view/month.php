<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_View_Month
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

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Schedule_Html_View_Week $viewWeek,
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

		$this->viewWeek = $hooks->wrap( $viewWeek );
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
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$shifts = $this->common->getShifts();

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$startDate = $this->t->setDateDb( $startDate )->setStartMonth()->formatDateDb();
		$endDate = $this->t->modify('+1 month')->modify('-1 day')->formatDateDb();

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

		$viewShifts = array();
		$iknow = array('date');

		$this->shiftsDuration->reset();
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

		$this->t->setDateDb( $startDate );
		$monthMatrix = $this->t->getMonthMatrix( $disabledWeekdays );

		$rows = array();
		foreach( $monthMatrix as $week => $days ){
			$row = array();

			foreach( $days as $date ){
				if( ! $date ){
					$row[] = NULL;
					continue;
				}

				$cell = array();

				$this->t->setDateDb( $date );

				$weekdayView = $this->t->getWeekdayName();
				$weekdayView = $this->ui->makeBlock( $weekdayView )->tag('muted')->tag('font-size', 1);

				$dayView = $this->t->getDay();

				$dateView = $this->ui->makeListInline( array($weekdayView, $dayView) )
					// ->gutter(0)
					->tag('align', 'center')
					;

				$toDateParams = array();
				$toDateParams['type'] = 'day';
				$toDateParams['start'] = $date;
				$toDate = array( $slug, $toDateParams );

				$weekLabel = $this->t->getWeekNo();
				$weekLabel = ' [' . '__Week__' . ' #' . $weekLabel . ']';

				$dateView = $this->ui->makeAhref( $toDate, $dateView )
					->tag('print')
					->addAttr( 'title', $weekLabel )
					;

				if( $date == $today ){
					$dateView = $this->ui->makeBlock( $dateView )
						->tag('border')
						->tag('border-color', 'gray')
						;
				}

				$cell[] = $dateView;

				if( isset($viewShifts[$date]) ){
					$shiftsView = $this->viewWeek->renderDay( $viewShifts[$date], $iknow );
					$cell[] = $shiftsView;
				}

				$to = 'new';
				$toParams = array(
					'date'	=> $date
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
			}

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( NULL, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			// ->setSegments( $weekSegments )
			;

		return $out;
	}

	public function renderByCalendar()
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$startDate = $this->t->setDateDb( $startDate )->setStartMonth()->formatDateDb();
		$endDate = $this->t->modify('+1 month')->modify('-1 day')->formatDateDb();

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

			$viewCalendars[ $calendar->getId() ] = $label;
		}

		$viewShifts = array();

		$iknow = array('calendar', 'date');
		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$id = $calendar ? $calendar->getId() : 0;

			if( ! array_key_exists($id, $viewShifts) ){
				$viewShifts[$id] = array();
			}

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
					if( ! array_key_exists($rexDate, $viewShifts[$id]) ){
						$viewShifts[$id][$rexDate] = array();
					}
					$viewShifts[$id][$rexDate][] = $shift;
				}

				if( ! $shiftAllDay ){
					break;
				}
				$this->t->modify( '+1 day' );
				$rexDate = $this->t->formatDateDb();
			}
		}

		$header = array();
		$header[] = NULL;

		$weekStartsOn = $this->t->getWeekStartsOn();
		$weekSegments = array();

		$this->t->setDateDb( $startDate );
		$monthMatrix = $this->t->getMonthMatrix( $disabledWeekdays );

		foreach( $monthMatrix as $week => $days ){
			$wdi = 0;
			foreach( $days as $date ){
				if( ! $date ){
					continue;
				}

				$this->t->setDateDb( $date );

				$dateView = array();
				$weekdayView = $this->t->getWeekdayName();
				$weekdayView = $this->ui->makeBlock( $weekdayView )->tag('muted')->tag('font-size', 1);
				$dateView[] = $weekdayView;

				$dayView = $this->t->getDay();
				$dateView[] = $dayView;

				$dateView = $this->ui->makeList($dateView)
					->gutter(0)
					->tag('align', 'center')
					;

				$toDateParams = array();
				$toDateParams['type'] = 'day';
				$toDateParams['start'] = $date;
				$toDate = array( $slug, $toDateParams );

				$weekLabel = $this->t->getWeekNo();
				$weekLabel = ' [' . '__Week__' . ' #' . $weekLabel . ']';

				$dateView = $this->ui->makeAhref( $toDate, $dateView )
					->tag('print')
					->addAttr( 'title', $weekLabel )
					;

				if( $date == $today ){
					$dateView = $this->ui->makeBlock( $dateView )
						->tag('border')
						->tag('border-color', 'gray')
						;
				}

				$header[] = $dateView;

				if( ! $wdi ){
					$weekSegments[] = count($header);
				}

				$wdi++;
			}
		}

		// while( count($header) < 32 ){
			// $header[] = NULL;
		// }

		$rows = array();
		foreach( $viewCalendars as $id => $calendarView ){
			$row = array();

			$this->shiftsDuration->reset();
			if( isset($viewShifts[$id]) ){
				foreach( $viewShifts[$id] as $date => $dateShifts ){
					foreach( $dateShifts as $shift ){
						$this->shiftsDuration->add( $shift );
					}
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
				->tag('padding', array('x1', 'y2'))
				->tag('nowrap')
				;
			$row[] = $calendarView;

			$countDates = count( $dates );
			foreach( $dates as $date ){
				$cell = array();

				if( isset($viewShifts[$id][$date]) ){
					if( $countDates <= 10 ){
						$shiftsView = $this->viewWeek->renderDay( $viewShifts[$id][$date], $iknow );
					}
					else {
						$shiftsView = $this->self->renderDay( $viewShifts[$id][$date], $iknow );
					}
					$cell[] = $shiftsView;
				}

				$label = '+';

				$to = 'new';
				$toParams = array(
					'date'	=> $date,
					'calendar'	=> $id,
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
						->addAttr('title', '__Add New__')
						;

					if( $today > $date ){
						$link->tag('muted', 3);
					}

					$cell[] = $link;
				}

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', '05')
					;

				$row[] = $cell;
			}

			// while( count($row) < 32 ){
				// $row[] = NULL;
			// }
			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			->setSegments( $weekSegments )
			->setLabelled( TRUE )
			;

		return $out;
	}

	public function renderByEmployee()
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$startDate = $this->t->setDateDb( $startDate )->setStartMonth()->formatDateDb();
		$endDate = $this->t->modify('+1 month')->modify('-1 day')->formatDateDb();

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
		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			$id = $employee ? $employee->getId() : 0;

			if( ! array_key_exists($id, $viewShifts) ){
				$viewShifts[$id] = array();
			}

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
					if( ! array_key_exists($rexDate, $viewShifts[$id]) ){
						$viewShifts[$id][$rexDate] = array();
					}
					$viewShifts[$id][$rexDate][] = $shift;
				}

				if( ! $shiftAllDay ){
					break;
				}
				$this->t->modify( '+1 day' );
				$rexDate = $this->t->formatDateDb();
			}
		}

		$header = array();
		$header[] = NULL;

		$weekStartsOn = $this->t->getWeekStartsOn();
		$weekSegments = array();

		$this->t->setDateDb( $startDate );
		$monthMatrix = $this->t->getMonthMatrix( $disabledWeekdays );

		foreach( $monthMatrix as $week => $days ){
			$wdi = 0;
			foreach( $days as $date ){
				if( ! $date ){
					continue;
				}
				$this->t->setDateDb( $date );

				$dateView = array();
				$weekdayView = $this->t->getWeekdayName();
				$weekdayView = $this->ui->makeBlock( $weekdayView )->tag('muted')->tag('font-size', 1);
				$dateView[] = $weekdayView;

				$dayView = $this->t->getDay();
				$dateView[] = $dayView;

				$dateView = $this->ui->makeList($dateView)
					->gutter(0)
					->tag('align', 'center')
					;

				$toDateParams = array();
				$toDateParams['type'] = 'day';
				$toDateParams['start'] = $date;
				$toDate = array( $slug, $toDateParams );

				$weekLabel = $this->t->getWeekNo();
				$weekLabel = ' [' . '__Week__' . ' #' . $weekLabel . ']';

				$dateView = $this->ui->makeAhref( $toDate, $dateView )
					->tag('print')
					->addAttr( 'title', $weekLabel )
					;

				if( $date == $today ){
					$dateView = $this->ui->makeBlock( $dateView )
						->tag('border')
						->tag('border-color', 'gray')
						;
				}

				$header[] = $dateView;

				if( ! $wdi ){
					$weekSegments[] = count($header);
				}

				$wdi++;
			}
		}

		// while( count($header) < 32 ){
			// $header[] = NULL;
		// }

		$rows = array();

		foreach( $viewEmployees as $id => $employeeView ){
			$row = array();

			$this->shiftsDuration->reset();
			if( isset($viewShifts[$id]) ){
				$counted = 0;
				foreach( $viewShifts[$id] as $date => $dateShifts ){
					foreach( $dateShifts as $shift ){
						$shiftCalendar = $shift->getCalendar();
						// if( $shiftCalendar->isTimeoff() ){
							// continue;
						// }
						$this->shiftsDuration->add( $shift );
						$counted++;
					}
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
				->tag('padding', array('x1', 'y2'))
				->tag('nowrap')
				;
			$row[] = $employeeView;

			$countDates = count( $dates );
			foreach( $dates as $date ){
				$cell = array();

				if( isset($viewShifts[$id][$date]) ){
					if( $countDates <= 10 ){
						$shiftsView = $this->viewWeek->renderDay( $viewShifts[$id][$date], $iknow );
					}
					else {
						$shiftsView = $this->self->renderDay( $viewShifts[$id][$date], $iknow );
					}
					$cell[] = $shiftsView;
				}

				$comboId = 0 . '-' . $id;
				if( isset($this->allCombos[$comboId]) ){
					$label = '+';

					$to = 'new';
					$toParams = array(
						'date'		=> $date,
						'employee'	=> $id,
						);
					$to = array( $to, $toParams );

					$link = $this->ui->makeAhref( $to, $label )
						->tag('tab-link')
						->tag('align', 'center')
						->addAttr('title', '__Add New__')
						;

					if( $today > $date ){
						$link->tag('muted', 3);
					}

					$cell[] = $link;
				}

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', '05')
					;

				$row[] = $cell;
			}

			// while( count($row) < 32 ){
				// $row[] = NULL;
			// }
			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			->setSegments( $weekSegments )
			->setLabelled( TRUE )
			;

		return $out;
	}

	public function renderDay( $shifts, $iknow )
	{
		$return = NULL;
		if( ! $shifts ){
			return $return;
		}

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

		$return = array();
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

			$thisView = $this->widget->renderCompact( $shift, $iknow, $groupedQty );

			if( ! $noZoom ){
				$to = 'shifts/' . $id;
				$thisView = $this->ui->makeAhref( $to, $thisView )
					->tag('unstyled-link')
					->tag('block')
					->tag('print')
					->addAttr('class', 'sh4-widget-loader')
					;
			}

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

				$checkbox = $this->ui->makeInputCheckbox( 'id[]', NULL, $id, TRUE );
				$checkbox = $this->ui->makeBlock( $checkbox )
					->addAttr('class', 'sh4-shift-checker')
					->addAttr('style', 'display: none;')
					;
				$thisView = $this->ui->makeCollection( array($thisView, $checkbox, $menu) );
			}

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('block')
				->addAttr('class', 'sh4-shift-widget')
				;

			$return[] = $thisView;
		}

		$return = $this->ui->makeList( $return )
			->gutter(1)
			;

		return $return;
	}
}