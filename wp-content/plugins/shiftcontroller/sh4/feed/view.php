<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Feed_View
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,
		HC3_Auth $auth,

		HC3_Translate $translate,
		SH4_Shifts_Presenter $shiftsPresenter,

		SH4_App_Query $appQuery,
		SH4_Shifts_Query $shiftsQuery
	)
	{
		$this->t = $t;
		$this->auth = $auth;
		$this->translate = $translate;

		$this->shiftsPresenter = $hooks->wrap( $shiftsPresenter );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
	}

	protected function _shifts( $token, $calendarId = NULL, $employeeId = NULL, $fromDate = NULL, $toDate = NULL )
	{
		$user = $this->auth->getUserByToken( $token );

		if( ! $user ){
			echo "wrong link";
			exit;
			return;
		}

		if( NULL === $fromDate ){
			$fromDate = $this->t->setNow()->formatDateDb();
			$toDate = $this->t->modify('+1 year')->formatDateDb();
		}

		$start = $this->t->setDateDb( $fromDate )->formatDateTimeDb();
		$end = $this->t->setDateDb( $toDate )->modify('+1 day')->formatDateTimeDb();

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;

		$shifts = $this->shiftsQuery->find();

	// filter shifts
		$ids = array_keys( $shifts );
		foreach( $ids as $id ){
			$shift = $shifts[$id];

			if( $shift->getStart() >= $end ){
				unset( $shifts[$id] );
				continue;
			}

			if( $shift->getEnd() <= $start ){
				unset( $shifts[$id] );
				continue;
			}

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
		return $shifts;
	}

	public function renderJson( $token, $calendarId = NULL, $employeeId = NULL, $fromDate = NULL, $toDate = NULL )
	{
		$shifts = $this->_shifts( $token, $calendarId, $employeeId, $fromDate, $toDate );

		$separator = ',';
		$out = array();
		foreach( $shifts as $shift ){
			$thisOut = $this->shiftsPresenter->export( $shift, TRUE );

			$keys = array_keys( $thisOut );
			reset( $keys );
			foreach( $keys as $k ){
				$thisOut[$k] = $this->translate->translate( $thisOut[$k] );
			}

			// $thisOut = HC3_Functions::buildCsv( $thisOut, $separator );
			$out[] = $thisOut;
		}

		$out = json_encode( $out );
		echo $out;
		exit;

		// echo $out;
		// exit;

		$fileName = 'feed';
		$fileName .= '-' . date('Y-m-d_H-i') . '.csv';
		HC3_Functions::pushDownload( $fileName, $out );
		exit;
	}

	public function render( $token, $calendarId = NULL, $employeeId = NULL, $fromDate = NULL, $toDate = NULL )
	{
		$shifts = $this->_shifts( $token, $calendarId, $employeeId, $fromDate, $toDate );

		$separator = ',';
		$out = array();
		foreach( $shifts as $shift ){
			$thisOut = $this->shiftsPresenter->export( $shift, TRUE );
			$header = array_keys( $thisOut );

			$keys = array_keys( $thisOut );
			reset( $keys );
			foreach( $keys as $k ){
				$thisOut[$k] = $this->translate->translate( $thisOut[$k] );
			}

			$thisOut = HC3_Functions::buildCsv( $thisOut, $separator );
			$out[] = $thisOut;
		}

		if( $out ){
			$header = HC3_Functions::buildCsv( $header, $separator );
			$header = array_unshift( $out, $header );
		}

		$out = join("\n", $out);

		// echo $out;
		// exit;

		$fileName = 'feed';
		$fileName .= '-' . date('Y-m-d_H-i') . '.csv';
		HC3_Functions::pushDownload( $fileName, $out );
		exit;
	}
}