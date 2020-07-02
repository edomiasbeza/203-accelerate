<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_View_Report
{
	public function __construct( 
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Shifts_DurationService $shiftsDurationService,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_Schedule_Html_View_Common $common
		)
	{
		$this->self = $hooks->wrap($this);

		$this->ui = $ui;
		$this->t = $t;
		$this->request = $request;

		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->common = $hooks->wrap( $common );
		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->shiftsDurationService = $hooks->wrap( $shiftsDurationService );
	}

	public function render()
	{
		$params = $this->request->getParams();
		$shifts = $this->common->getShifts();

		$header = array(
			'qty'		=> '__Number Of Shifts__',
			'duration'	=> '__Hours__',
			);

		$rows = array();

		$this->shiftsDuration->reset();
		foreach( $shifts as $shift ){
			$this->shiftsDuration->add( $shift );
		}

		$row = array();
		$row['qty'] = $this->shiftsDuration->getQty();
		$row['duration'] = $this->shiftsDuration->formatDuration();

		$rows[] = $row;

		$out = $this->ui->makeTable( $header, $rows );

		return $out;
	}

	public function renderByCalendar()
	{
		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();

		$header = array(
			'label'		=> NULL,
			'qty'		=> '__Number Of Shifts__',
			'duration'	=> '__Hours__',
			);

		$viewBy = array();
		$rows = array();

		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$rows[ $calendarId ] = array('label' => '', 'qty' => 0, 'duration' => 0);

			// $label = $this->calendarsPresenter->presentTitle( $calendar );
			$label = $calendar->getTitle();
			$rows[ $calendarId ]['label'] = $label;

			$viewBy[ $calendarId ] = array();
		}

		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$calendarId = $calendar->getId();

			if( ! array_key_exists($calendarId, $viewBy) ){
				continue;
			}
			$viewBy[ $calendarId ][] = $shift;
		}

		reset( $viewBy );
		foreach( $viewBy as $calendarId => $thisShifts ){
			$this->shiftsDuration->reset();
			foreach( $thisShifts as $shift ){
				$this->shiftsDuration->add( $shift );
			}

			$rows[ $calendarId ]['qty'] = $this->shiftsDuration->getQty();
			$rows[ $calendarId ]['duration'] = $this->shiftsDuration->formatDuration();
		}

		$out = $this->ui->makeTable( $header, $rows );
		return $out;
	}

	public function renderByEmployee()
	{
		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();

		$header = array(
			'label'		=> NULL,
			);

		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$calendarView = $this->calendarsPresenter->presentTitle( $calendar );
			$header['calendar_' . $calendarId] = $calendarView;
		}
		$header['total'] = '__Total__';

		$viewBy = array();
		$rows = array();

		foreach( $employees as $employee ){
			$employeeId = $employee ? $employee->getId() : 0;
			$rows[ $employeeId ] = array('label' => '', 'qty' => 0, 'duration' => 0);

			$label = $employee->getTitle();
			$label = $this->ui->makeSpan( $label )
				->tag('font-size', 4)
				;
			$rows[ $employeeId ]['label'] = $label;

			$viewBy[ $employeeId ] = array();
		}

		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			$employeeId = $employee ? $employee->getId() : 0;

			if( ! array_key_exists($employeeId, $viewBy) ){
				continue;
			}
			$viewBy[ $employeeId ][] = $shift;
		}

		reset( $viewBy );
		foreach( $viewBy as $employeeId => $thisShifts ){
			$durations = array();

			$durations[0] = $this->shiftsDurationService->newCounter();
			reset( $calendars );
			foreach( $calendars as $calendar ){
				$calendarId = $calendar->getId();
				$durations[ $calendarId ] = $this->shiftsDurationService->newCounter();
			}

			reset( $thisShifts );
			foreach( $thisShifts as $shift ){
				$calendar = $shift->getCalendar();
				$calendarId = $calendar->getId();
				if( ! array_key_exists($calendarId, $durations) ){
					continue;
				}
				$durations[$calendarId]->add( $shift );
				$durations[0]->add( $shift );
			}

			reset( $calendars );
			foreach( $calendars as $calendar ){
				$calendarId = $calendar->getId();
				$rows[ $employeeId ]['calendar_' . $calendarId] = $durations[$calendarId]->formatDuration();
			}
			$rows[ $employeeId ]['total'] = $durations[0]->formatDuration();
		}

		$out = $this->ui->makeTable( $header, $rows );

		return $out;
	}
}