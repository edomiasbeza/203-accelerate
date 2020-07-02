<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Schedule_Html_View_ICommon
{
	public function getShifts();
	public function getCalendars();
	public function getEmployees();

	public function findAllCalendars();
	public function findAllEmployees();
	public function filterShifts( $shifts );
	public function filterViewTypes( $types );

	public function renderReport( $shiftsDuration, $alignRight = TRUE );
}

class SH4_Schedule_Html_View_Common implements SH4_Schedule_Html_View_ICommon
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Auth $auth,
		HC3_Settings $settings,

		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,

		SH4_App_Query $appQuery,

		SH4_Calendars_Permissions $calendarsPermissions,
		SH4_Employees_Query $employeesQuery,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Shifts_Query $shiftsQuery
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->auth = $auth;

		$this->ui = $ui;
		$this->t = $t;
		$this->request = $request;
		$this->settings = $settings;

		$this->appQuery = $hooks->wrap( $appQuery );

		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
	}

	public function renderReport( $shiftsDuration, $alignRight = TRUE )
	{
		$return = NULL;

		$hide = $this->settings->get('datetime_hide_schedule_reports');
		if( $hide ){
			return $return;
		}

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();

		if( ! $currentUserId ){
			return $return;
		}

		$qty = $shiftsDuration->getQty();
		$duration = $shiftsDuration->getDuration();

		$qtyTimeoff = $shiftsDuration->getQtyTimeoff();
		$durationTimeoff = $shiftsDuration->getDurationTimeoff();

		if( $qtyTimeoff ){
			$qty = $qty - $qtyTimeoff;
			$duration = $duration - $durationTimeoff;
		}

		$return = array();
		// $return[] = $this->ui->makeBlockInline( $shiftsDuration->getQty() )
		// 	->addAttr( 'title', '__Number Of Shifts__' )
		// 	;
		$return[] = $this->ui->makeSpan( $shiftsDuration->formatDuration($duration) )
			->addAttr( 'title', '__Hours__' )
			;
		$return[] = $this->ui->makeBlockInline( '(' . $qty . ')' )
			->addAttr( 'title', '__Shifts__' )
			->tag('muted')
			->tag('font-size', 2)
			;
		$return = $this->ui->makeListInline( $return )
			->gutter(1)
			;

		if( $qtyTimeoff ){
			$timeoffReturn = array();
			$timeoffReturn[] = $this->ui->makeSpan( $shiftsDuration->formatDuration($durationTimeoff) )
				->addAttr( 'title', '__Hours__' )
				;
			$timeoffReturn[] = $this->ui->makeBlockInline( '(' . $qtyTimeoff . ')' )
				->addAttr( 'title', '__Time Off__' )
				->tag('muted')
				->tag('font-size', 2)
				;
			$timeoffReturn = $this->ui->makeListInline( $timeoffReturn )
				->gutter(1)
				;

			$return = $this->ui->makeListInline( array($return, $timeoffReturn) )
				->gutter(1)
				;
		}

		$return = $this->ui->makeBlockInline( $return )
			->tag('border')
			->tag('padding', 1 )
			;
		if( $alignRight ){
			$return = $this->ui->makeBlock( $return )
				->tag('align', 'right')
				;
		}

		return $return;
	}

	public function filterShifts( $return )
	{
		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();

		$meEmployeeId = NULL;
		$meEmployeeCalendars = array();

		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( $meEmployee ){
			$meEmployeeId = $meEmployee->getId();
			$meEmployeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
		}

		$myManagedCalendars = array();
		$myViewedCalendars = array();
		if( $currentUserId ){
			$myManagedCalendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );
			$myViewedCalendars = $this->appQuery->findCalendarsViewedByUser( $currentUser );
		}

		$ids = array_keys( $return );
		foreach( $ids as $id ){
			$shift = $return[$id];

			$shiftCalendar = $shift->getCalendar();
			$shiftCalendarId = $shiftCalendar->getId();
			$shiftEmployee = $shift->getEmployee();
			$shiftEmployeeId = $shiftEmployee->getId();

		// is manager?
			if( isset($myManagedCalendars[$shiftCalendarId]) ){
				continue;
			}

		// is viewer?
			if( isset($myViewedCalendars[$shiftCalendarId]) ){
				continue;
			}

		// is visitor
			if( ! $meEmployeeId ){
				if( $shift->isOpen() ){
					$permName = $shift->isPublished() ? 'visitor_view_open_publish' : 'visitor_view_open_draft';
				}
				else {
					$permName = $shift->isPublished() ? 'visitor_view_others_publish' : 'visitor_view_others_draft';
				}
				$perm = $this->calendarsPermissions->get( $shiftCalendar, $permName );
				if( ! $perm ){
					unset( $return[$id] );
				}
				continue;
			}

		// is shift employee
			if( $meEmployeeId == $shiftEmployeeId ){
				$permName = $shift->isPublished() ? 'employee_view_own_publish' : 'employee_view_own_draft';
				$perm = $this->calendarsPermissions->get( $shiftCalendar, $permName );
				if( ! $perm ){
					unset( $return[$id] );
				}
				continue;
			}

		// is other employee
			if( $meEmployeeId != $shiftEmployeeId ){
			// calendar employees
				if( isset($meEmployeeCalendars[$shiftCalendarId]) ){
					if( $shift->isOpen() ){
						$permName = $shift->isPublished() ? 'employee_view_open_publish' : 'employee_view_open_draft';
					}
					else {
						$permName = $shift->isPublished() ? 'employee_view_others_publish' : 'employee_view_others_draft';
					}
				}
			// other employees
				else {
					if( $shift->isOpen() ){
						$permName = $shift->isPublished() ? 'employee2_view_open_publish' : 'employee2_view_open_draft';
					}
					else {
						$permName = $shift->isPublished() ? 'employee2_view_others_publish' : 'employee2_view_others_draft';
					}
				}

				$perm = $this->calendarsPermissions->get( $shiftCalendar, $permName );
				if( ! $perm ){
					unset( $return[$id] );
				}

				continue;
			}
		}

	// sort
		uasort( $return, array($this, '_sortShifts') );

		return $return;
	}

	public function _sortShifts( $a, $b )
	{
		$compare1 = $a->getStart();
		$compare2 = $b->getStart();
		if( $compare1 != $compare2 ){
			return ( $compare1 > $compare2 );
		}

		$compare1 = $a->getEnd();
		$compare2 = $b->getEnd();
		if( $compare1 != $compare2 ){
			return ( $compare1 > $compare2 );
		}

		$cal1 = $a->getCalendar();
		$cal2 = $b->getCalendar();
		if( ! ($cal1 && $cal2) ){
			return;
		}

		$compare1 = $cal1->getTitle();
		$compare2 = $cal2->getTitle();
		if( $compare1 != $compare2 ){
			return strcmp( $compare2, $compare1 );
		}

		$emp1 = $a->getEmployee();
		$emp2 = $b->getEmployee();
		if( ! ($emp1 && $emp2) ){
			return;
		}

		$compare1 = $emp1->getTitle();
		$compare2 = $emp2->getTitle();
		if( $compare1 != $compare2 ){
			return strcmp( $compare2, $compare1 );
		}
	}

	public function filterViewTypes( $return )
	{
		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			unset( $return['report'] );
		}
		return $return;
	}

	public function findAllEmployees()
	{
		$return = $this->employeesQuery->findActive();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();

		$employee = $this->appQuery->findEmployeeByUser( $currentUser );
		$managedCalendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );

		$allowed = array();
		if( $employee ){
			$allowed[ $employee->getId() ] = $employee;
		}

		$calendars = $this->self->findAllCalendars();
		foreach( $calendars as $calendar ){
			$thisCalendarId = $calendar->getId();

			$thisAllowed = $this->appQuery->findEmployeesForCalendar( $calendar );

			if( isset($managedCalendars[$thisCalendarId]) ){
				$allowed = $allowed + $thisAllowed;
				continue;
			}

		// OPEN?
			if( isset($thisAllowed[0]) ){
				$permNames = array();
				$permNames[] = 'visitor_view_open_publish';
				$permNames[] = 'visitor_view_open_draft';
				if( $employee ){
					$permNames[] = 'employee_view_open_publish';
					$permNames[] = 'employee_view_open_draft';
				}

				reset( $permNames );
				foreach( $permNames as $permName ){
					$perm = $this->calendarsPermissions->get( $calendar, $permName );
					if( $perm ){
						$allowed[0] = $thisAllowed[0];
						break;
					}
				}

				unset( $thisAllowed[0] );
			}

			$permNames = array();
			$permNames[] = 'visitor_view_others_publish';
			$permNames[] = 'visitor_view_others_draft';
			if( $employee ){
				$permNames[] = 'employee_view_others_publish';
				$permNames[] = 'employee_view_others_draft';
			}

			reset( $permNames );
			foreach( $permNames as $permName ){
				$perm = $this->calendarsPermissions->get( $calendar, $permName );
				if( $perm ){
					$allowed = $allowed + $thisAllowed;
					break;
				}
			}

		// PICKUP?
			if( $employee ){
				$permNames = array();
				$permNames[] = 'employee_pickup_others';

				reset( $permNames );
				foreach( $permNames as $permName ){
					$perm = $this->calendarsPermissions->get( $calendar, $permName );
					if( $perm ){
						$allowed = $allowed + $thisAllowed;
						break;
					}
				}
			}

			// $allowed = $allowed + $thisReturn;
		}

		$ids = array_keys( $return );
		foreach( $ids as $id ){
			if( ! array_key_exists($id, $allowed) ){
				unset( $return[$id] );
			}
		}

		return $return;
	}

	public function findAllCalendars()
	{
		$return = $this->calendarsQuery->findActive();

		$allowed = array();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		$employee = $this->appQuery->findEmployeeByUser( $currentUser );

		if( $currentUserId ){
			$thisReturn = $this->appQuery->findCalendarsManagedByUser( $currentUser );
			$allowed = $allowed + $thisReturn;

			$thisReturn = $this->appQuery->findCalendarsViewedByUser( $currentUser );
			$allowed = $allowed + $thisReturn;
		}

		$calendarsForEmployee = array();
		if( $employee ){
			$calendarsForEmployee = $this->appQuery->findCalendarsForEmployee( $employee );
			$allowed = $allowed + $calendarsForEmployee;
		}

		foreach( $return as $calendar ){
			$calendarId = $calendar->getId();

			$permNames = array();
			$permNames[] = 'visitor_view_others_publish';
			$permNames[] = 'visitor_view_others_draft';

			if( $currentUserId ){
				// if( ! isset($calendarsForEmployee[$currentUserId]) ){
					// continue;
				// }

				if( isset($calendarsForEmployee[$currentUserId]) ){
					$permNames[] = 'employee_view_others_publish';
					$permNames[] = 'employee_view_others_draft';
				}
				else {
					$permNames[] = 'employee2_view_others_publish';
					$permNames[] = 'employee2_view_others_draft';
				}
			}

			reset( $permNames );
			foreach( $permNames as $permName ){
				$perm = $this->calendarsPermissions->get( $calendar, $permName );
				if( $perm ){
					$allowed[ $calendarId ] = $calendar;
					break;
				}
			}
		}

		$ids = array_keys( $return );
		foreach( $ids as $id ){
			// if( ! array_key_exists($id, $allowed) ){
			if( ! isset($allowed[$id]) ){
				unset( $return[$id] );
			}
		}

		return $return;
	}

	public function getShifts()
	{
	// init params
		$params = $this->request->getParams();
		if( isset($params['time']) && ('now' == $params['time']) ){
			$params['time'] = $this->t->setNow()->formatDateTimeDb();
		}

		$type = $params['type'];
		$startDate = $params['start'];
		$endDate = $params['end'];

		if( FALSE !== strpos($endDate, '+') ){
			$this->t->setDateDb($startDate)->modify($endDate);
			$endDate = $this->t->formatDateDb();
		}

		$exactDateTime = isset( $params['time'] ) ? $params['time'] : NULL;
		if( $exactDateTime ){
			$startDate = $this->t->setDateTimeDb( $exactDateTime )->formatDateDb();
			$endDate = $startDate;
		}

		switch( $type ){
			case 'day':
				$this->t->setDateDb($startDate);
				$start = $this->t->formatDateTimeDb();

				$startDate = $this->t->formatDateDb();
				$this->request->setParam('start', $startDate);
				$endDate = $this->t->formatDateDb();
				$this->request->setParam('end', $endDate);

				// $this->t->modify('+1 day');
				$this->t->modify('+7 days');
				$end = $this->t->formatDateTimeDb();
				break;

			case 'week':
				$this->t->setDateDb($startDate)->setStartWeek();
				$startDate = $this->t->formatDateDb();
				$this->request->setParam('start', $startDate);

				$start = $this->t->formatDateTimeDb();
				$this->t->modify('+1 week')
					// ->modify('-1 day')
					;
				$endDate = $this->t->formatDateDb();
				$this->request->setParam('end', $endDate);

				$end = $this->t->formatDateTimeDb();
				break;

			case '4weeks':
				$this->t->setDateDb($startDate)->setStartWeek();
				$startDate = $this->t->formatDateDb();
				$this->request->setParam('start', $startDate);

				$start = $this->t->formatDateTimeDb();
				$this->t->modify('+4 weeks')
					// ->modify('-1 day')
					;
				$endDate = $this->t->formatDateDb();
				$this->request->setParam('end', $endDate);

				$end = $this->t->formatDateTimeDb();
				break;

			case 'month':
				$this->t->setDateDb($startDate)->setStartMonth();
				$startDate = $this->t->formatDateDb();
				$this->request->setParam('start', $startDate);

				$start = $this->t->formatDateTimeDb();
				$this->t->modify('+1 month')
					// ->modify('-1 day')
					;
				$endDate = $this->t->formatDateDb();
				$this->request->setParam('end', $endDate);

				$end = $this->t->formatDateTimeDb();
				break;

			default:
				if( $exactDateTime ){
					$start = $exactDateTime;
					$end = $exactDateTime;
				}
				else {
					$this->t->setDateDb($startDate);
					$start = $this->t->formatDateTimeDb();

					$this->t->setDateDb($endDate)->modify('+1 day');
					$end = $this->t->formatDateTimeDb();
				}


				break;
		}

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;

		$return = $this->shiftsQuery->find();

	// filter
		$employees = $this->getEmployees();
		$calendars = $this->getCalendars();

		foreach( array_keys($return) as $id ){
			$shift = $return[$id];

			$calendar = $shift->getCalendar();
			if( ! array_key_exists($calendar->getId(), $calendars) ){
				unset( $return[$id] );
				continue;
			}

			$employee = $shift->getEmployee();
			if( ! array_key_exists($employee->getId(), $employees) ){
				unset( $return[$id] );
				continue;
			}

			$shiftStart = $shift->getStart();
			$shiftEnd = $shift->getEnd();

			if( $shiftEnd <= $start ){
				unset( $return[$id] );
				continue;
			}

			if( $shiftStart >= $end ){
				unset( $return[$id] );
				continue;
			}

			// if( $shiftStart < $start ){
			// 	unset( $return[$id] );
			// 	continue;
			// }
		}

		$return = $this->self->filterShifts( $return );

		return $return;
	}

	public function getCalendars()
	{
		$return = $this->self->findAllCalendars();
		$params = $this->request->getParams();

		if( $params['calendar'] ){
			$filterIds = $params['calendar'];

			$ids = array_keys($return);
			foreach( $ids as $id ){
				if( ! in_array($id, $filterIds) ){
					unset( $return[$id] );
				}
			}
		}

		if( $params['employee'] ){
			$employees = $this->self->findAllEmployees();

			$filterEmployeeIds = $params['employee'];
			$filter = array();
			foreach( $employees as $employee ){
				$employeeId = $employee->getId();
				if( ! (in_array(-1, $filterEmployeeIds) && ($employeeId > 0)) ){
					if( ! in_array($employeeId, $filterEmployeeIds) ){
						continue;
					}
				}
				$filter = $filter + $this->appQuery->findCalendarsForEmployee( $employee );
			}
			$filterIds = array_keys( $filter );

			$ids = array_keys($return);
			foreach( $ids as $id ){
				if( ! in_array($id, $filterIds) ){
					unset( $return[$id] );
				}
			}
		}

		return $return;
	}

	public function getEmployees()
	{
		$return = $this->self->findAllEmployees();
		$params = $this->request->getParams();

		if( $params['employee'] ){
			$filterIds = $params['employee'];

			$ids = array_keys($return);
			foreach( $ids as $id ){
				if( in_array(-1, $filterIds) && ($id > 0) ){
					continue;
				}

				if( ! in_array($id, $filterIds) ){
					unset( $return[$id] );
				}
			}
		}

		if( $params['calendar'] ){
			$calendars = $this->self->findAllCalendars();

			$filter = array();
			foreach( $calendars as $calendar ){
				if( ! in_array($calendar->getId(), $params['calendar']) ){
					continue;
				}
				$filter = $filter + $this->appQuery->findEmployeesForCalendar( $calendar );
			}
			$filterIds = array_keys( $filter );

			$ids = array_keys($return);
			foreach( $ids as $id ){
				if( ! in_array($id, $filterIds) ){
					unset( $return[$id] );
				}
			}
		}

		return $return;
	}
}