<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_View_List
{
	public function __construct( 
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Shifts_View_Widget $widget,
		SH4_Schedule_Html_View_Common $common,
		SH4_Shifts_View_Common $shiftsCommon
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->ui = $ui;
		$this->t = $t;
		$this->request = $request;

		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->widget = $hooks->wrap( $widget );
		$this->common = $hooks->wrap( $common );
		$this->shiftsCommon = $hooks->wrap( $shiftsCommon );
	}

	public function render()
	{
		$params = $this->request->getParams();
		$startDate = $params['start'];

		$hideui = $params['hideui'];
		$noZoom = in_array('shiftdetails', $hideui);

		$allEmployees = $this->common->findAllEmployees();
		$allCalendars = $this->common->findAllCalendars();

		$shifts = $this->common->getShifts();

		$iknow = array();
		$hori = TRUE;

		$this->shiftsDuration->reset();
		$thisOut = array();
		foreach( $shifts as $shift ){
			$id = $shift->getId();
			$thisView = $this->renderShift( $shift, $iknow, $hori, $noZoom );

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

			$thisOut[] = $thisView;

			$this->shiftsDuration->add( $shift );
		}

		$out = $this->ui->makeList( $thisOut );

		return $out;
	}

	public function renderByCalendar()
	{
		$params = $this->request->getParams();
		$startDate = $params['start'];

		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();

		$shifts = $this->common->getShifts();

		$hideui = $params['hideui'];
		$noZoom = in_array('shiftdetails', $hideui);

		$viewCalendars = array();
		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			// $label = $this->calendarsPresenter->presentTitle( $calendar );
			$label = $calendar->getTitle();
			$viewCalendars[ $calendarId ] = $label;
		}

		$shiftsBy = array();

		$iknow = array( 'calendar' );
		$hori = TRUE;

		$allEmployees = $this->common->findAllEmployees();
		if( count($allEmployees) < 2 ){
			$iknow[] = 'employee';
		}

		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$calendarId = $calendar ? $calendar->getId() : 0;
			if( ! array_key_exists($calendarId, $shiftsBy) ){
				$shiftsBy[$calendarId] = array();
			}
			$shiftsBy[$calendarId][] = $shift;
		}

		$out = array();
		foreach( $viewCalendars as $calendarId => $calendarView ){
			if( ! isset($shiftsBy[$calendarId]) ){
				continue;
			}

			if( ! isset($calendars[$calendarId]) ){
				continue;
			}

			$thisCalendar = $calendars[$calendarId];
			$thisOut = array();

			$this->shiftsDuration->reset();
			$outShifts = array();
			foreach( $shiftsBy[$calendarId] as $shift ){
				$id = $shift->getId();
				$thisView = $this->renderShift( $shift, $iknow, $hori, $noZoom );

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
				$outShifts[] = $thisView;

				$this->shiftsDuration->add( $shift );
			}
			$outShifts = $this->ui->makeList( $outShifts )->gutter(1);

			$thisOut[] = $outShifts;

		// report
			$outReport = $this->common->renderReport( $this->shiftsDuration );

		// new links
			$links = array();

			if( $thisCalendar->isTimeOff() ){
				$label = '+' . ' ' . '__Time Off__';
			}
			elseif( $thisCalendar->isAvailability() ){
				$label = '+' . ' ' . '__Availability__';
			}
			else {
				$label = '+' . ' ' . '__Shift__';
			}

			$to = 'new';
			$toParams = array(
				'calendar'	=> $calendarId
				);
			if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
				$toParams['employee'] = $params['employee'][0];
			}

			$to = array( $to, $toParams );
			$newLink = $this->ui->makeAhref( $to, $label )
				->tag('secondary')
				;
			$links[] = $newLink;

			$links = $this->ui->makeListInline( $links )
				->gutter(1)
				;

			$calendarView = $this->ui->makeListInline( array($calendarView, $links) );
			$onTop = $this->ui->makeGrid()
				->add( $calendarView, 4, 12 )
				->add( $outReport, 8, 12 )
				;

			$thisOut = $this->ui->makeList( $thisOut );
			$thisOut = $this->ui->makeList( array($onTop, $thisOut) );

			$out[] = $thisOut;
		}

		$out = $this->ui->makeList( $out )
			->gutter(4)
			;

		return $out;
	}

	public function renderByEmployee()
	{
		$params = $this->request->getParams();
		$startDate = $params['start'];

		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();

		$shifts = $this->common->getShifts();

		$hideui = $params['hideui'];
		$noZoom = in_array('shiftdetails', $hideui);

		$viewEmployees = array();
		foreach( $employees as $employee ){
			$label = $employee->getTitle();
			$label = $this->ui->makeSpan( $label )
				->tag('font-size', 5)
				;
			$viewEmployees[ $employee->getId() ] = $label;
		}

		$shiftsBy = array();

		$iknow = array( 'employee' );
		$hori = TRUE;

		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			$employeeId = $employee ? $employee->getId() : 0;
			if( ! array_key_exists($employeeId, $shiftsBy) ){
				$shiftsBy[$employeeId] = array();
			}
			$shiftsBy[$employeeId][] = $shift;
		}

		$out = array();
		foreach( $viewEmployees as $employeeId => $employeeView ){
			if( ! isset($shiftsBy[$employeeId]) ){
				continue;
			}

			$thisOut = array();

			$this->shiftsDuration->reset();
			$counted = 0;
			$outShifts = array();

			foreach( $shiftsBy[$employeeId] as $shift ){
				$id = $shift->getId();
				$thisView = $this->renderShift( $shift, $iknow, $hori, $noZoom );

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

				$outShifts[] = $thisView;

				$shiftCalendar = $shift->getCalendar();
				// if( ! $shiftCalendar->isShift() ){
					// continue;
				// }
				$this->shiftsDuration->add( $shift );
				$counted++;
			}

			$outShifts = $this->ui->makeList( $outShifts )->gutter(1);
			$thisOut[] = $outShifts;

		// report
			$outReport = NULL;
			if( $counted ){
				$outReport = $this->common->renderReport( $this->shiftsDuration );
			}

		// new links
			$newLinks = array();

			$label = '+' . ' ' . '__Shift__';
			$to = 'new/shift';
			$toParams = array(
				'employee'	=> $employeeId
				);
			$to = array( $to, $toParams );
			$link = $this->ui->makeAhref( $to, $label )
				->tag('secondary')
				;
			$newLinks[] = $link;

			$label = '+' . ' ' . '__Time Off__';
			$to = 'new/timeoff';
			$toParams = array(
				'employee'	=> $employeeId
				);
			$to = array( $to, $toParams );
			$link = $this->ui->makeAhref( $to, $label )
				->tag('secondary')
				;
			$newLinks[] = $link;

			// $label = '+' . ' ' . '__Availability__';
			// $to = 'new/availability';
			// $toParams = array(
				// 'employee'	=> $employeeId
				// );
			// $to = array( $to, $toParams );
			// $link = $this->ui->makeAhref( $to, $label )
				// ->tag('secondary')
				// ;
			// $newLinks[] = $link;

			$newLinks = $this->ui->makeListInline( $newLinks )
				->gutter(1)
				;

			$employeeView = $this->ui->makeListInline( array($employeeView, $newLinks) );
			$onTop = $this->ui->makeGrid()
				->add( $employeeView, 4, 12 )
				->add( $outReport, 8, 12 )
				;

			$thisOut = $this->ui->makeList( $thisOut );
			$thisOut = $this->ui->makeList( array($onTop, $thisOut) );

			$out[] = $thisOut;
		}

		$out = $this->ui->makeList( $out )
			->gutter(4)
			;

		return $out;
	}

	public function renderShift( $shift, $iknow, $hori )
	{
		$params = $this->request->getParams();

		if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
			if( ! in_array(-1, $params['employee']) ){
				$iknow[] = 'employee';
			}
		}
		if( array_key_exists('calendar', $params) && (count($params['calendar']) == 1) ){
			$iknow[] = 'calendar';
		}

		$out = $this->widget->render( $shift, $iknow, $hori );
		return $out;
	}
}