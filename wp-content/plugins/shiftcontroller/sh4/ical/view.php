<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Ical_View
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,
		HC3_Auth $auth,

		SH4_App_Query $appQuery,
		SH4_Shifts_Query $shiftsQuery,
		SH4_Ical_Lib_iCalCreator $ical
	)
	{
		$this->t = $t;
		$this->ical = $ical;
		$this->auth = $auth;

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->self = $hooks->wrap( $this );
	}

	public function renderDescription( SH4_Shifts_Model $shift )
	{
		$calendar = $shift->getCalendar();
		$employee = $shift->getEmployee();

		$calendarView = $calendar->getTitle();
		$employeeView = $employee->getTitle();

		$return = $employeeView . ' @ ' . $calendarView;
		return $return;
	}

	public function render( $token, $calendarId = NULL, $employeeId = NULL )
	{
		$user = $this->auth->getUserByToken( $token );

		if( ! $user ){
			echo "wrong link";
			exit;
			return;
		}

	/* 1 month before and 3 months after */
		$start = $this->t->setNow()->modify('-1 month')->formatDateTimeDb();
		$end = $this->t->setNow()->modify('+3 months')->formatDateTimeDb();

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;

		$shifts = $this->shiftsQuery->find();

	// filter shifts
		$ids = array_keys( $shifts );
		foreach( $ids as $id ){
			$shift = $shifts[$id];

			$shiftCalendar = $shift->getCalendar();
			$shiftCalendarId = $shiftCalendar->getId();

			$shiftEmployee = $shift->getEmployee();
			$shiftEmployeeId = $shiftEmployee->getId();

			if( (NULL !== $calendarId) && ('x' != $calendarId) ){
				if( (NULL !== $calendarId) && ('x' != $calendarId) ){
					if( $shiftCalendarId != $calendarId ){
						unset( $shifts[$id] );
					}
				}
			}
			else {
				if( (NULL !== $employeeId) && ('x' != $employeeId) ){
					if( $shiftEmployeeId != $employeeId ){
						unset( $shifts[$id] );
					}
				}
			}
		}

		$shifts = $this->appQuery->filterShiftsForUser( $user, $shifts );

		$timezoneObj = $this->t->getTimezone();
		$timezone = $timezoneObj->getName();

		$t2 = clone $this->t;
		$t2->setTimezone('UTC');

		$myUnique = 'shiftcontroller';

		$cal = $this->ical;

		$cal->setConfig( 'unique_id', $myUnique );
//		$cal->setProperty( 'method', 'publish' );
		$cal->setProperty( 'method', 'request' );
		$cal->setProperty( 'x-wr-timezone', $timezone );

		$vtz = new hc_vtimezone();
		$vtz->setProperty( 'tzid', $timezone );
		$cal->addComponent( $vtz );

		reset( $shifts );
		foreach( $shifts as $shift ){
			$shiftId = $shift->getId();
			$calendar = $shift->getCalendar();
			$employee = $shift->getEmployee();
			$start = $shift->getStart();
			$end = $shift->getEnd();

			$calendarView = $calendar->getTitle();
			$employeeView = $employee->getTitle();

			$event = new hc_vevent(); // initiate a new EVENT
			$event->setProperty( 'uid', 'obj-' . $shiftId . '-' . $myUnique );

			$summary = $employeeView . ' @ ' . $calendarView;
			$description = $this->self->renderDescription( $shift, $user );

			$isMultiDay = $shift->isMultiDay();

			$this->t->setDateTimeDb( $start );
			$t2->setTimestamp( $this->t->getTimestamp() );

			if( $isMultiDay ){
				$date = $t2->formatDateDb();
				$event->setProperty( 'dtstart', $date, array('VALUE' => 'DATE'));
			}
			else {
				list( $year, $month, $day, $hour, $min ) = $t2->getParts(); 
				$event->setProperty( 'dtstart', $year, $month, $day, $hour, $min, 00, 'Z' );  // 24 dec 2006 19.30
			}

			$this->t->setDateTimeDb( $end );
			$t2->setTimestamp( $this->t->getTimestamp() );

			if( $isMultiDay ){
				$date = $t2->formatDateDb();
				$event->setProperty( 'dtend', $date, array('VALUE' => 'DATE'));
			}
			else {
				list( $year, $month, $day, $hour, $min ) = $t2->getParts(); 
				$event->setProperty( 'dtend', $year, $month, $day, $hour, $min, 00, 'Z' );  // 24 dec 2006 19.30
			}

			// $event->setProperty( 'location', $calendarView );
			$event->setProperty( 'description', $description );
			$event->setProperty( 'summary', $summary );

			if( $shift->isPublished() ){
				$event->setProperty( 'status', 'TENTATIVE' );
			}
			else {
				$event->setProperty( 'status', 'CONFIRMED' );
			}

			$cal->addComponent( $event );
		}

		$return = $cal->createCalendar();

		// header('Content-Type: text/calendar');
		echo $return;
		exit;
	}
}