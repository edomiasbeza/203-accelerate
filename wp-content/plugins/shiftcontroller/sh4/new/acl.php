<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_New_IAcl
{
	public function checkDraft( $calendarId, $typeId, $dateString, $employeeString );
	public function checkPublish( $calendarId, $typeId, $dateString, $employeeString );

	public function checkNewTimeoff( $params = array() );
	public function checkNewAvailability( $params = array() );
	public function checkNewShift( $params = array() );
	public function checkNew( $params = array() );

	public function check( $calendarId, $typeId, $employeeId, $date );
	public function findAllCombos();
}

class SH4_New_Acl implements SH4_New_IAcl
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,

		SH4_App_Query $appQuery,
		SH4_Calendars_Permissions $calendarsPermissions,
		SH4_ShiftTypes_Query $shiftTypesQuery,

		SH4_Shifts_Availability $availability,
		SH4_New_Query $newQuery,
		SH4_Calendars_Query $calendarsQuery,
		HC3_Auth $auth,
		HC3_IPermission $permission
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->t = $t;

		$this->availability = $hooks->wrap( $availability );
		$this->newQuery = $hooks->wrap( $newQuery );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap( $permission );
		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );
	}

	public function checkCreate( $calendarId, $typeId, $dateString, $employeeString )
	{
		$return = FALSE;

		$return = $this->self->checkDraft( $calendarId, $typeId, $dateString, $employeeString );
		if( $return ){
			return $return;
		}

		$return = $this->self->checkPublish( $calendarId, $typeId, $dateString, $employeeString );
		return $return;
	}

	public function checkDraft( $calendarId, $typeId, $dateString, $employeeString )
	{
		$return = FALSE;

		$employeesIds = HC3_Functions::unglueArray( $employeeString );
		$dates = HC3_Functions::unglueArray( $dateString );

		foreach( $dates as $date ){
			foreach( $employeesIds as $employeeId ){
				$pos = strpos($employeeId, '-');
				if( $pos ){
					$employeeId = substr($employeeId, 0, $pos);
				}

				$preCheck = $this->self->check( $calendarId, $typeId, $employeeId, $date );
				if( ! $preCheck ){
					return $return;
				}
			}
		}

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		if( $this->permission->isAdmin($currentUser) ){
			$return = TRUE;
			return $return;
		}

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( isset($calendarsAsManager[$calendarId]) ){
			$return = TRUE;
			return $return;
		}

		$calendarsAsEmployee = array();
		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( $meEmployee ){
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
			foreach( $employeeCalendars as $thisCalendar ){
				$thisCalendarId = $thisCalendar->getId();

				if( $this->calendarsPermissions->get($thisCalendar, 'employee_create_own_draft') ){
					$calendarsAsEmployee[ $thisCalendarId ] = $thisCalendar;
				}
			}
		}

		if( $calendarId != 'x' ){
			if( isset($calendarsAsEmployee[$calendarId]) ){
				$return = TRUE;
			}
		}
		else {
			if( $calendarsAsEmployee ){
				$return = TRUE;
			}
		}

		if( $return ){
			// check if can create shifts with conflicts
			// if( ! $this->calendarsPermissions->get($thisCalendar, 'employee_create_own_conflicts') ){
				// $return = FALSE;
			// }
		}

		return $return;
	}

	public function checkPublish( $calendarId, $typeId, $dateString, $employeeString )
	{
		$return = FALSE;
		$employeesIds = HC3_Functions::unglueArray( $employeeString );
		$dates = HC3_Functions::unglueArray( $dateString );

		foreach( $dates as $date ){
			foreach( $employeesIds as $employeeId ){
				$pos = strpos($employeeId, '-');
				if( $pos ){
					$employeeId = substr($employeeId, 0, $pos);
				}

				$preCheck = $this->self->check( $calendarId, $typeId, $employeeId, $date );
				if( ! $preCheck ){
					$return = FALSE;
					return $return;
				}
			}
		}

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		if( $this->permission->isAdmin($currentUser) ){
			$return = TRUE;
			return $return;
		}

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( isset($calendarsAsManager[$calendarId]) ){
			$return = TRUE;
			return $return;
		}

		$calendarsAsEmployee = array();
		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( $meEmployee ){
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
			foreach( $employeeCalendars as $thisCalendar ){
				$thisCalendarId = $thisCalendar->getId();

				$checkPerms = array( 'employee_create_own_publish' );
				foreach( $checkPerms as $perm ){
					if( $this->calendarsPermissions->get($thisCalendar, $perm) ){
						$calendarsAsEmployee[ $thisCalendarId ] = $thisCalendar;
						break;
					}
				}
			}
		}

		if( $calendarId != 'x' ){
			if( isset($calendarsAsEmployee[$calendarId]) ){
				$return = TRUE;
			}
		}
		else {
			if( $calendarsAsEmployee ){
				$return = TRUE;
			}
		}

		return $return;
	}

	public function checkNew( $params = array() )
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		if( $this->permission->isAdmin($currentUser) ){
			$return = TRUE;
			return $return;
		}

		if( array_key_exists('date', $params) ){
			$checkDate = $params['date'];

			$today = $this->t->setNow()->formatDateDb();

			if( $checkDate < $today ){
				$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );

				if( array_key_exists('calendar', $params) ){
					$calendarId = $params['calendar'];
					if( ! isset($calendarsAsManager[$calendarId]) ){
						return $return;
					}
				}
				else {
					if( ! $calendarsAsManager ){
						return $return;
					}
				}
			}
		}

$useCache = TRUE;
static $cache = array();
$cacheParams = $params;
unset( $cacheParams['date'] );
$paramsString = http_build_query($cacheParams, NULL, ',');

if( $useCache ){
	if( isset($cache[$paramsString]) ){
		// echo "USE CACHE '$paramsString'<br>";
		$return = $cache[$paramsString];
		return $return;
	}
}

		if( ! $return ){
			$return = $this->self->checkNewShift( $params );
		}

		if( ! $return ){
			$return = $this->self->checkNewTimeoff( $params );
		}

		if( ! $return ){
			$return = $this->self->checkNewAvailability( $params );
		}

		$cache[$paramsString] = $return;

		return $return;
	}

	public function checkNewTimeoff( $params = array() )
	{
		$return = FALSE;

// echo "CHECKING TIMEOFF";
// _print_r( $params );

		$calendars = $this->_findCalendars( $params );
		if( $calendars === FALSE ){
			$return = FALSE;
			return $return;
		}

		$employeeIds = array('x');
		if( array_key_exists('employee', $params) ){
			$employeeIds = HC3_Functions::unglueArray( $params['employee'] );
		}

		foreach( $calendars as $calendar ){
			if( ! $calendar->isTimeoff() ){
				continue;
			}

			$thisCalendarId = $calendar->getId();

			$thisReturn = TRUE;
			foreach( $employeeIds as $employeeId ){
				$pos = strpos($employeeId, '-');
				if( $pos ){
					$employeeId = substr($employeeId, 0, $pos);
				}

				$thisThisReturn = $this->self->check( $thisCalendarId, 'x', $employeeId );
				if( ! $thisThisReturn ){
					$thisReturn = FALSE;
					break;
				}
			}

			if( $thisReturn ){
				$return = TRUE;
				break;
			}
		}

		return $return;
	}

	public function checkNewAvailability( $params = array() )
	{
		if( ! $this->availability->hasAvailability() ){
			$return = FALSE;
			return $return;
		}

		$return = FALSE;

		$calendars = $this->_findCalendars( $params );
		if( $calendars === FALSE ){
			$return = FALSE;
			return $return;
		}

		$employeeIds = array('x');
		if( array_key_exists('employee', $params) ){
			$employeeIds = HC3_Functions::unglueArray( $params['employee'] );
		}

		foreach( $calendars as $calendar ){
			if( ! $calendar->isAvailability() ){
				continue;
			}

			$thisCalendarId = $calendar->getId();

			$thisReturn = TRUE;
			foreach( $employeeIds as $employeeId ){
				$pos = strpos($employeeId, '-');
				if( $pos ){
					$employeeId = substr($employeeId, 0, $pos);
				}

				$thisThisReturn = $this->self->check( $thisCalendarId, 'x', $employeeId );
				if( ! $thisThisReturn ){
					$thisReturn = FALSE;
					break;
				}
			}

			if( $thisReturn ){
				$return = TRUE;
				break;
			}
		}

		return $return;
	}

	public function checkNewShift( $params = array() )
	{
// static $count = 0;
// $count++;

// echo __METHOD__ . ': ' . $count . '<br>';
// _print_r( $params );

		$return = FALSE;

		$calendars = $this->_findCalendars( $params );
		if( $calendars === FALSE ){
			$return = FALSE;
			return $return;
		}

		$employeeIds = array('x');
		if( array_key_exists('employee', $params) ){
			$employeeIds = HC3_Functions::unglueArray( $params['employee'] );
		}

		foreach( $calendars as $calendar ){
			if( ! $calendar->isShift() ){
				continue;
			}

			$thisCalendarId = $calendar->getId();
			$thisReturn = TRUE;
			foreach( $employeeIds as $employeeId ){
				$pos = strpos($employeeId, '-');
				if( $pos ){
					$employeeId = substr($employeeId, 0, $pos);
				}

				$thisThisReturn = $this->self->check( $thisCalendarId, 'x', $employeeId );
				if( ! $thisThisReturn ){
					$thisReturn = FALSE;
					break;
				}
			}

			if( $thisReturn ){
				$return = TRUE;
				break;
			}
		}

		return $return;
	}

	protected function _findCalendars( $params = array() )
	{
		$return = $this->newQuery->findAllCalendars();

		$employees = array();
		if( array_key_exists('employee', $params) ){
			$allEmployees = $this->newQuery->findAllEmployees();

			$employeeId = $params['employee'];
			$employeeIds = HC3_Functions::unglueArray( $employeeId );
			foreach( $employeeIds as $employeeId ){
				$pos = strpos($employeeId, '-');
				if( $pos ){
					$employeeId = substr($employeeId, 0, $pos);
				}

				if( array_key_exists($employeeId, $allEmployees) ){
					$employees[] = $allEmployees[$employeeId];
				}
				else {
					$return = FALSE;
					return $return;
				}
			}
		}

		if( $employees ){
			foreach( $employees as $employee ){
				$filter = $this->appQuery->findCalendarsForEmployee( $employee );

				$filterIds = array_keys( $filter );
				$ids = array_keys($return);
				foreach( $ids as $id ){
					if( ! in_array($id, $filterIds) ){
						unset( $return[$id] );
					}
				}
			}
		}

		if( array_key_exists('calendar', $params) ){
			$ok = FALSE;

			$suppliedCalendarIds = is_array($params['calendar']) ? $params['calendar'] : array($params['calendar']);
			foreach( $suppliedCalendarIds as $suppliedCalendarId ){
				if( isset($return[$suppliedCalendarId]) ){
					$ok = TRUE;
					break;
				}
			}

			if( ! $ok ){
				$return = FALSE;
				return $return;
			}
		}

		return $return;
	}

	public function check( $calendarId, $typeId, $employeeId, $dateString = NULL )
	{
		$return = FALSE;
		$today = $this->t->setNow()->formatDateDb();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		if( $this->permission->isAdmin($currentUser) ){
			$return = TRUE;
			return $return;
		}

		$pos = strpos($employeeId, '-');
		if( $pos ){
			$employeeId = substr($employeeId, 0, $pos);
		}

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );

		$calendarsAsEmployee = array();
		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( $meEmployee ){
			$meEmployeeId = $meEmployee->getId();
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
			foreach( $employeeCalendars as $thisCalendar ){
				$thisCalendarId = $thisCalendar->getId();
				$checkPerms = array( 'employee_create_own_draft', 'employee_create_own_publish' );
				foreach( $checkPerms as $perm ){
					if( $this->calendarsPermissions->get($thisCalendar, $perm) ){
						$calendarsAsEmployee[ $thisCalendarId ] = $thisCalendar;
						break;
					}
				}
			}
		}

		if( ($calendarId == 'x') && ($typeId =='x') && ($employeeId == 'x') ){
			if( $calendarsAsEmployee OR $calendarsAsManager ){
				$return = TRUE;
			}
			return $return;
		}

	// check calendar
		if( $calendarId != 'x' ){
			if( ! (isset($calendarsAsManager[$calendarId]) OR isset($calendarsAsEmployee[$calendarId])) ){
				return $return;
			}
		}

	// check shift type
		if( $typeId != 'x' ){
			if( $calendarId == 'x' ){
				return $return;
			}

			$calendar = $this->calendarsQuery->findActiveById( $calendarId );
			if( ! $calendar ){
				return $return;
			}

			$calendarShiftTypes = $this->appQuery->findShiftTypesForCalendar( $calendar );

		// custom time
			$checkTypeId = $typeId;
			if( strpos($typeId, '-') ){
				$checkTypeId = 0;
			}
			if( ! isset($calendarShiftTypes[$checkTypeId]) ){
				return $return;
			}
		}

	// check employee
		if( $employeeId != 'x' ){
			if( $calendarsAsManager ){
				foreach( $calendarsAsManager as $calendar ){
					$calendarEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
					if( isset($calendarEmployees[$employeeId]) ){
						$return = TRUE;
						return $return;
					}
				}
			}

// echo "CHECK $calendarId, $typeId, $employeeId, $date<br>";
// echo "ME EMPLOYEE $meEmployeeId VS $employeeId<br>";

			if( $meEmployee && ($meEmployeeId == $employeeId) && $calendarsAsEmployee ){
				if( $dateString ){
					$checkDates = HC3_Functions::unglueArray( $dateString );
					foreach( $checkDates as $checkDate ){
						if( $checkDate < $today ){
							$return = FALSE;
							return $return;
						}
					}
				}

				$return = TRUE;
			}

			return $return;
		}

		if( $calendarId != 'x' ){
			if( isset($calendarsAsManager[$calendarId]) OR isset($calendarsAsEmployee[$calendarId]) ){
				$return = TRUE;
				return $return;
			}
		}

		// echo "CALENDAR ID = $calendarId, TYPE Id = $typeId, EMPLOYEE ID = $employeeId<br>";
		return $return;
	}

	public function findAllCombos()
	{
		$ret = array();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $ret;
		}

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		foreach( $calendarsAsManager as $calendar ){
			$calendarEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
			foreach( $calendarEmployees as $employee ){
				$retId = $calendar->getId() . '-' . $employee->getId();
				$ret[ $retId ] = $retId;
				$retId = 0 . '-' . $employee->getId();
				$ret[ $retId ] = $retId;
			}
		}

		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );

		if( $meEmployee ){
			$calendarsAsEmployee = array();
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
			foreach( $employeeCalendars as $thisCalendar ){
				$thisCalendarId = $thisCalendar->getId();
				if( $this->calendarsPermissions->get($thisCalendar, 'employee_create_own_draft') ){
					$calendarsAsEmployee[ $thisCalendarId ] = $thisCalendar;
				}
			}

			foreach( $calendarsAsEmployee as $calendar ){
				$retId = $calendar->getId() . '-' . $meEmployee->getId();
				$ret[ $retId ] = $retId;
			}
		}

		return $ret;
	}

	public function findAllCombosTimeoff()
	{
		$ret = array();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $ret;
		}

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		foreach( $calendarsAsManager as $calendar ){
			if( ! $calendar->isTimeoff() ){
				continue;
			}
			$calendarEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
			foreach( $calendarEmployees as $employee ){
				$retId = $calendar->getId() . '-' . $employee->getId();
				$ret[ $retId ] = $retId;
				$retId = 0 . '-' . $employee->getId();
				$ret[ $retId ] = $retId;
			}
		}

		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );

		if( $meEmployee ){
			$calendarsAsEmployee = array();
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
			foreach( $employeeCalendars as $calendar ){
				if( ! $calendar->isTimeoff() ){
					continue;
				}
				$thisCalendarId = $calendar->getId();
				if( $this->calendarsPermissions->get($calendar, 'employee_create_own_draft') ){
					$calendarsAsEmployee[ $thisCalendarId ] = $calendar;
				}
			}

			foreach( $calendarsAsEmployee as $calendar ){
				$retId = $calendar->getId() . '-' . $meEmployee->getId();
				$ret[ $retId ] = $retId;
			}
		}

		return $ret;
	}

	public function findAllCombosShift()
	{
		$ret = array();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $ret;
		}

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		foreach( $calendarsAsManager as $calendar ){
			if( ! $calendar->isShift() ){
				continue;
			}

			$calendarEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
			foreach( $calendarEmployees as $employee ){
				$retId = $calendar->getId() . '-' . $employee->getId();
				$ret[ $retId ] = $retId;
				$retId = 0 . '-' . $employee->getId();
				$ret[ $retId ] = $retId;
			}
		}

		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );

		if( $meEmployee ){
			$calendarsAsEmployee = array();
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
			foreach( $employeeCalendars as $calendar ){
				if( ! $calendar->isShift() ){
					continue;
				}
				$thisCalendarId = $calendar->getId();
				if( $this->calendarsPermissions->get($calendar, 'employee_create_own_draft') ){
					$calendarsAsEmployee[ $thisCalendarId ] = $calendar;
				}
			}

			foreach( $calendarsAsEmployee as $calendar ){
				$retId = $calendar->getId() . '-' . $meEmployee->getId();
				$ret[ $retId ] = $retId;
			}
		}

		return $ret;
	}
}