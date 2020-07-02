<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_App_IQuery
{
	public function findUserByEmployee( SH4_Employees_Model $employee );
	public function findEmployeeByUser( HC3_Users_Model $user );
	public function findAllUsersWithEmployee();

	public function findManagersForCalendar( SH4_Calendars_Model $calendar );
	public function findCalendarsManagedByUser( HC3_Users_Model $user );
	public function findCalendarsViewedByUser( HC3_Users_Model $user );

	public function findEmployeesForCalendar( SH4_Calendars_Model $calendar );
	public function findCalendarsForEmployee( SH4_Employees_Model $employee );

	public function findShiftTypesForCalendar( SH4_Calendars_Model $calendar );

	public function filterShiftsForUser( HC3_Users_Model $user, array $shifts );
}

class SH4_App_Query implements SH4_App_IQuery
{
	public function __construct(
		HC3_Settings $settings,
		HC3_IPermission $permission,

		SH4_ShiftTypes_Query $shiftTypesQuery,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Employees_Query $employeesQuery,
		SH4_Calendars_Permissions $cp,

		HC3_Users_Query $usersQuery,
		HC3_CrudFactory $crudFactory,
		HC3_Hooks $hooks
		)
	{
		$this->settings = $hooks->wrap( $settings );
		$this->permission = $hooks->wrap( $permission );
		$this->crudFactory = $hooks->wrap( $crudFactory );

		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->usersQuery = $hooks->wrap( $usersQuery );
		$this->cp = $hooks->wrap( $cp );

		$this->self = $hooks->wrap( $this );
	}

	public function filterShiftsForUser( HC3_Users_Model $user, array $return )
	{
		$currentUserId = $user->getId();

		if( ! $currentUserId ){
			$return = array();
			return $return;
		}

		$calendarsAsManager = $this->self->findCalendarsManagedByUser( $user );
		$calendarsAsViewer = $this->self->findCalendarsViewedByUser( $user );

		$calendarsAsEmployee = array();
		$meEmployee = $this->self->findEmployeeByUser( $user );
		if( $meEmployee ){
			$meEmployeeId = $meEmployee->getId();
			$calendarsAsEmployee = $this->self->findCalendarsForEmployee( $meEmployee );
		}

		$ids = array_keys( $return );

		foreach( $ids as $id ){
			$shift = $return[$id];

			$shiftCalendar = $shift->getCalendar();
			$shiftCalendarId = $shiftCalendar->getId();
			$shiftEmployee = $shift->getEmployee();
			$shiftEmployeeId = $shiftEmployee->getId();

			if( isset($calendarsAsManager[$shiftCalendarId]) ){
				continue;
			}

			if( isset($calendarsAsViewer[$shiftCalendarId]) ){
				continue;
			}

			if( isset($calendarsAsEmployee[$shiftCalendarId]) ){
				if( $shiftEmployeeId == $meEmployeeId ){
					if( $shift->isPublished() ){
						$perm = 'employee_view_own_publish';
					}
					else {
						$perm = 'employee_view_own_draft';
					}
				}
				else {
					if( $shift->isOpen() ){
						if( $shift->isPublished() ){
							$perm = 'employee_view_open_publish';
						}
						else {
							$perm = 'employee_view_open_draft';
						}
					}
					else {
						if( $shift->isPublished() ){
							$perm = 'employee_view_others_publish';
						}
						else {
							$perm = 'employee_view_others_draft';
						}
					}
				}

				if( $this->cp->get($shiftCalendar, $perm) ){
					continue;
				}
			}

			if( $shift->isOpen() ){
				if( $shift->isPublished() ){
					$perm = 'visitor_view_open_publish';
				}
				else {
					$perm = 'visitor_view_open_draft';
				}
			}
			else {
				if( $shift->isPublished() ){
					$perm = 'visitor_view_others_publish';
				}
				else {
					$perm = 'visitor_view_others_draft';
				}
			}

			if( $this->cp->get($shiftCalendar, $perm) ){
				continue;
			}

			unset( $return[$id] );
		}

		return $return;
	}

	public function findCalendarsManagedByUser( HC3_Users_Model $user )
	{
		$return = $this->calendarsQuery->findActive();

		$isAdmin = $this->permission->isAdmin( $user );
		if( $isAdmin ){
			return $return;
		}

		$userId = $user->getId();
		foreach( $return as $calendar ){
			$calendarId = $calendar->getId();
			$managers = $this->self->findManagersForCalendar( $calendar );
			if( ! isset($managers[$userId])){
				unset($return[$calendarId]);
			}
		}

		return $return;
	}

	public function findCalendarsViewedByUser( HC3_Users_Model $user )
	{
		$return = $this->calendarsQuery->findActive();

		$isAdmin = $this->permission->isAdmin( $user );
		if( $isAdmin ){
			return $return;
		}

		$userId = $user->getId();
		foreach( $return as $calendar ){
			$calendarId = $calendar->getId();
			$viewers = $this->self->findViewersForCalendar( $calendar );
			if( ! isset($viewers[$userId])){
				unset($return[$calendarId]);
			}
		}

		return $return;
	}

	public function findManagersForCalendar( SH4_Calendars_Model $calendar )
	{
		$return = $this->permission->findAdmins();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_manager';

		$usersIds = $this->settings->get( $settingName, TRUE );
		if( $usersIds ){
			$moreReturn = $this->usersQuery->findManyById( $usersIds );
			foreach( $moreReturn as $id => $user ){
				if( ! array_key_exists($id, $return) ){
					$return[ $id ] = $user;
				}
			}
		}

		return $return;
	}

	public function findViewersForCalendar( SH4_Calendars_Model $calendar )
	{
		// $return = $this->permission->findAdmins();
		$return = array();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_viewer';

		$usersIds = $this->settings->get( $settingName, TRUE );
		if( $usersIds ){
			$moreReturn = $this->usersQuery->findManyById( $usersIds );
			foreach( $moreReturn as $id => $user ){
				if( ! array_key_exists($id, $return) ){
					$return[ $id ] = $user;
				}
			}
		}

		return $return;
	}

	public function findEmployeesForCalendar( SH4_Calendars_Model $calendar )
	{
		$return = array();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_employee';

		$employeesIds = $this->settings->get( $settingName, TRUE );
		if( $employeesIds ){
			$return = $this->employeesQuery->findManyActiveById( $employeesIds );
		}

		return $return;
	}

	public function findShiftTypesForCalendar( SH4_Calendars_Model $calendar )
	{
		$return = array();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_shifttype';

		$shiftTypesIds = $this->settings->get( $settingName, TRUE );
		if( $shiftTypesIds ){
			$return = $this->shiftTypesQuery->findManyById( $shiftTypesIds );
		}

		return $return;
	}

	public function findCalendarsForEmployee( SH4_Employees_Model $employee )
	{
		$return = array();
		$employeeId = $employee->getId();

		$ids = array();
		$calendars = $this->calendarsQuery->findActive();

		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$settingName = 'calendar_' . $calendarId . '_employee';
			$employeesIds = $this->settings->get( $settingName, TRUE );
			if( in_array($employeeId, $employeesIds) ){
				$return[ $calendarId ] = $calendar;
			}
		}
		return $return;
	}

	public function findAllUsersWithEmployee()
	{
		$return = array();

		$crud = $this->crudFactory->make('employee');

		$args = array();
		$results = $crud->read( $args );
		if( ! $results ){
			return $return;
		}

		$usersIds = array();
		foreach( $results as $r ){
			$userId = array_key_exists('user_id', $r) ? $r['user_id'] : NULL;
			if( ! $userId ){
				continue;
			}
			$usersIds[ $userId ] = $userId;
		}

		if( ! $usersIds ){
			return $return;
		}

		$return = $this->usersQuery->findManyById( $usersIds );
		return $return;
	}

	public function findUserByEmployee( SH4_Employees_Model $employee )
	{
		$return = NULL;

		$employeeId = $employee->getId();
		if( ! $employeeId ){
			return $return;
		}

		$crud = $this->crudFactory->make('employee');
		$args = array();
		$args[] = array('id', '=', $employeeId );
		$results = $crud->read( $args );

		if( ! $results ){
			return $return;
		}

		$results = array_shift( $results );
		$userId = array_key_exists('user_id', $results) ? $results['user_id'] : NULL;

		if( ! $userId ){
			return $return;
		}

		$return = $this->usersQuery->findById( $userId );
		return $return;
	}

	public function findEmployeeByUser( HC3_Users_Model $user )
	{
		$return = NULL;

		$userId = $user->getId();
		if( ! $userId ){
			return $return;
		}

		$crud = $this->crudFactory->make('employee');
		$args = array();
		$args[] = array('user_id', '=', $userId );
		$results = $crud->read( $args );

		if( ! $results ){
			return $return;
		}

		$results = array_shift( $results );
		$employeeId = array_key_exists('id', $results) ? $results['id'] : NULL;

		if( ! $employeeId ){
			return $return;
		}

		$return = $this->employeesQuery->findById( $employeeId );
		return $return;
	}
}