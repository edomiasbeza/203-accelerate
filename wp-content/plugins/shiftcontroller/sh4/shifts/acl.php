<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_IAcl
{
	public function checkView( $shiftId );
	public function checkCreate( $shiftId );
	public function checkCreateDraft( $shiftId );
	public function checkCreatePublished( $shiftId );
	public function checkManager( $shiftId );
	public function checkEmployeeAssignment( $ids, $employeeId );
	public function checkChangeTime( $shiftId );
}

class SH4_Shifts_Acl implements SH4_Shifts_IAcl
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Settings $settings,

		SH4_App_Query $appQuery,
		SH4_Calendars_Permissions $calendarsPermissions,

		SH4_Shifts_Query $shiftsQuery,
		HC3_Auth $auth,
		HC3_IPermission $permission
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->settings = $hooks->wrap( $settings );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap( $permission );
		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );

		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
	}

	public function checkDelete( $shiftId )
	{
		$return = FALSE;

		$shift = $this->shiftsQuery->findById( $shiftId );

		if( $shift->isDraft() ){
			if( $this->self->checkCreateDraft($shiftId) ){
				$return  = TRUE;
			}
		}
		else {
			if( $this->self->checkCreatePublished($shiftId) ){
				$return  = TRUE;
			}
		}

		return $return;
	}

	public function checkCreate( $shiftId )
	{
		$return = FALSE;

		if( $this->self->checkCreateDraft($shiftId) ){
			$return  = TRUE;
			return $return;
		}

		if( $this->self->checkCreatePublished($shiftId) ){
			$return  = TRUE;
			return $return;
		}

		return $return;
	}

	public function checkCreatePublished( $shiftId )
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

		$shift = $this->shiftsQuery->findById( $shiftId );
		$calendar = $shift->getCalendar();
		$calendarId = $calendar->getId();

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( isset($calendarsAsManager[$calendarId]) ){
			$return = TRUE;
			return $return;
		}

		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );

		if( ! $meEmployee ){
			return $return;
		}

		$shiftEmployee = $shift->getEmployee();
		$shiftEmployeeId = $shiftEmployee->getId();

		$meEmployeeId = $meEmployee->getId();

		if( $meEmployeeId != $shiftEmployeeId ){
			return $return;
		}

		$calendarsAsEmployee = array();

		$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
		foreach( $employeeCalendars as $thisCalendar ){
			$thisCalendarId = $thisCalendar->getId();

			if( $this->calendarsPermissions->get($thisCalendar, 'employee_create_own_publish') ){
				$calendarsAsEmployee[ $thisCalendarId ] = $thisCalendar;
			}
		}

		if( isset($calendarsAsEmployee[$calendarId]) ){
			$return = TRUE;
			return $return;
		}

		return $return;
	}

	public function checkChangeTime( $shiftId )
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$shift = $this->shiftsQuery->findById( $shiftId );
		if( $shift->isMultiDay() ){
			return $return;
		}

		if( $shift->isPublished() ){
			return $this->self->checkCreatePublished( $shiftId );
		}
		else {
			return $this->self->checkCreateDraft( $shiftId );
		}
	}

	public function checkCreateDraft( $shiftId )
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$noDraft = $this->settings->get('shifts_no_draft') ? TRUE : FALSE;
		if( $noDraft ){
			return $return;
		}

		if( $this->permission->isAdmin($currentUser) ){
			$return = TRUE;
			return $return;
		}

		$shift = $this->shiftsQuery->findById( $shiftId );
		$calendar = $shift->getCalendar();
		$calendarId = $calendar->getId();

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( isset($calendarsAsManager[$calendarId]) ){
			$return = TRUE;
			return $return;
		}

		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );

		if( ! $meEmployee ){
			return $return;
		}

		$shiftEmployee = $shift->getEmployee();
		$shiftEmployeeId = $shiftEmployee->getId();

		$meEmployeeId = $meEmployee->getId();

		if( $meEmployeeId != $shiftEmployeeId ){
			return $return;
		}

		$calendarsAsEmployee = array();
		$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
		foreach( $employeeCalendars as $thisCalendar ){
			$thisCalendarId = $thisCalendar->getId();
			if( $this->calendarsPermissions->get($thisCalendar, 'employee_create_own_draft') ){
				$calendarsAsEmployee[ $thisCalendarId ] = $thisCalendar;
			}
		}

		if( isset($calendarsAsEmployee[$calendarId]) ){
			$return = TRUE;
			return $return;
		}

		return $return;
	}

	public function checkManager( $ids )
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

		$ids = HC3_Functions::unglueArray( $ids );
		$shifts = $this->shiftsQuery->findManyById( $ids );

		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$calendarId = $calendar->getId();

			$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );

		// check calendar
			if( ! isset($calendarsAsManager[$calendarId]) ){
				return $return;
			}
		}

		$return = TRUE;
		return $return;
	}

	public function checkEmployeeAssignment( $ids, $employeeId )
	{
		$return = FALSE;

		if( ! $this->self->checkManager( $ids ) ){
			return  $return;
		}

	// check if open shifts are allowed
		if( ! $employeeId ){
			$ids = HC3_Functions::unglueArray( $ids );
			$shifts = $this->shiftsQuery->findManyById( $ids );

			foreach( $shifts as $shift ){
				$calendar = $shift->getCalendar();

				$employees = $this->appQuery->findEmployeesForCalendar( $calendar );
				if( ! isset($employees[0]) ){
					return $return;
				}
			}
		}

		$return = TRUE;
		return $return;
	}

	public function checkView( $shiftId )
	{
		$return = FALSE;

		$shift = $this->shiftsQuery->findById( $shiftId );
		$calendar = $shift->getCalendar();
		$calendarId = $calendar->getId();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			if( $shift->isOpen() ){
				$permName = $shift->isPublished() ? 'visitor_view_open_publish' : 'visitor_view_open_draft';
			}
			else {
				$permName = $shift->isPublished() ? 'visitor_view_others_publish' : 'visitor_view_others_draft';
			}

			$perm = $this->calendarsPermissions->get( $calendar, $permName );
			if( $perm ){
				$return = TRUE;
			}

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

		$calendarsAsViewer = $this->appQuery->findCalendarsViewedByUser( $currentUser );
		if( isset($calendarsAsViewer[$calendarId]) ){
			$return = TRUE;
			return $return;
		}

		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );

		if( ! $meEmployee ){
			return $return;
		}

		$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
		// if( ! isset($employeeCalendars[$calendarId]) ){
			// return $return;
		// }

		$shiftEmployee = $shift->getEmployee();
		$shiftEmployeeId = $shiftEmployee->getId();

		$meEmployeeId = $meEmployee->getId();

		if( $meEmployeeId == $shiftEmployeeId ){
			$return = TRUE;
			return $return;
		}

		if( isset($employeeCalendars[$calendarId]) ){
			if( $shift->isOpen() ){
				$permName = $shift->isPublished() ? 'employee_view_open_publish' : 'employee_view_open_draft';
			}
			else {
				$permName = $shift->isPublished() ? 'employee_view_others_publish' : 'employee_view_others_draft';
			}
		}
		else {
			if( $shift->isOpen() ){
				$permName = $shift->isPublished() ? 'employee2_view_open_publish' : 'employee2_view_open_draft';
			}
			else {
				$permName = $shift->isPublished() ? 'employee2_view_others_publish' : 'employee2_view_others_draft';
			}
		}
		$perm = $this->calendarsPermissions->get( $calendar, $permName );

		if( $perm ){
			$return = TRUE;
		}

		return $return;
	}
}