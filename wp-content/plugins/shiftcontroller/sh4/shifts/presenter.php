<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_Presenter_
{
	public function presentTitle( SH4_Shifts_Model $model );
	public function presentFullTime( SH4_Shifts_Model $model );
	public function presentDate( SH4_Shifts_Model $model );
	public function presentTime( SH4_Shifts_Model $model );
	public function presentRawTime( SH4_Shifts_Model $model );
	public function presentBreak( SH4_Shifts_Model $model );

	public function presentStartDate( SH4_Shifts_Model $model );
	public function presentStartTime( SH4_Shifts_Model $model );
	public function presentEndDate( SH4_Shifts_Model $model );
	public function presentEndTime( SH4_Shifts_Model $model );
	public function presentStatus( SH4_Shifts_Model $model );

	public function export( SH4_Shifts_Model $model, $withContacts = FALSE );
}

class SH4_Shifts_Presenter implements SH4_Shifts_Presenter_
{
	protected $shiftTypes = array();

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,

		SH4_App_Query $appQuery,
		HC3_Settings $settings,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter,
		SH4_ShiftTypes_Query $shiftTypes
		)
	{
		$this->t = $t;
		$this->self = $hooks->wrap($this);

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );

		$this->shiftTypes = array();
		if( $settings->get('shifttypes_show_title') ){
			$this->shiftTypes = $hooks->wrap( $shiftTypes )->findAll();
		}
	}

	public function presentFullTime( SH4_Shifts_Model $model )
	{
		$return = array();

		$start = $model->getStart();
		$end = $model->getEnd();

		$this->t->setDateTimeDb($start);
		$startDate = $this->t->formatDateDb();
		$startTs = $this->t->getTimestamp();
		$this->t->setStartDay();
		$startDay = $this->t->getTimestamp();
		$startInDay = $startTs - $startDay;

		$this->t->setDateTimeDb($end);
		$endTs = $this->t->getTimestamp();
		$this->t->setStartDay();
		$startEndDay = $this->t->getTimestamp();
		$endInDay = $endTs - $startEndDay;

		if( (0 == $startInDay) && (0 == $endInDay) ){
			$endDate = $this->t->setDateTimeDb($end)->modify('-1 second')->formatDateDb();
			$return = $this->t->formatDateRange( $startDate, $endDate );
		}
		else {
			$return[] = $this->self->presentDate($model);
			$return[] = $this->self->presentTime($model);
			$return = join(' ', $return);
		}

		return $return;
	}

	public function presentTitle( SH4_Shifts_Model $model )
	{
		$return = array();

		$return[] = $model->getCalendar()->getTitle();
		$return[] = $this->self->presentFullTime($model);
		$return[] = $model->getEmployee()->getTitle();

		$return = join(' &middot; ', $return );
		return $return;
	}

	public function presentDate( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$return = $this->t->formatDate();
		return $return;
	}

	public function presentRawTime( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$start = $this->t->formatTime();

		$this->t->setDateTimeDb( $model->getEnd() );
		$end = $this->t->formatTime();
		$endInDay = $model->getEndInDay();
		if( $endInDay > 24*60*60 ){
			$end = '&gt;' . $end;
		}

		$return = $start . ' - ' . $end;
		return $return;
	}

	public function presentBreak( SH4_Shifts_Model $model )
	{
		$return = NULL;
		$breakStart = $model->getBreakStart();
		if( NULL === $breakStart ){
			return $return;
		}

		$this->t->setDateTimeDb( $model->getBreakStart() );
		$start = $this->t->formatTime();

		$this->t->setDateTimeDb( $model->getBreakEnd() );
		$end = $this->t->formatTime();

		$return = $start . ' - ' . $end;
		return $return;
	}

	public function presentTime( SH4_Shifts_Model $model )
	{
		// $this->t->setDateTimeDb( $model->getStart() );
		// $start = $this->t->formatTime();

		// $this->t->setDateTimeDb( $model->getEnd() );
		// $end = $this->t->formatTime();

		// $return = $start . ' - ' . $end;

		$return = $this->self->presentRawTime( $model );

		$this->t->setDateTimeDb( $model->getStart() );
		$startTs = $this->t->getTimestamp();
		$this->t->setStartDay();
		$startDay = $this->t->getTimestamp();
		$startInDay = $startTs - $startDay;

		$this->t->setDateTimeDb( $model->getEnd() );
		$endTs = $this->t->getTimestamp();
		$endInDay = $endTs - $startDay;

		$timeInDay = $startInDay . '-' . $endInDay;

	// if we have a matching template
		reset( $this->shiftTypes );
		foreach( $this->shiftTypes as $shiftType ){
			$thisTimeInDay = $shiftType->getStart() . '-' . $shiftType->getEnd();

			if( $thisTimeInDay == $timeInDay ){
				$return = $shiftType->getTitle();
				break;
			}
		}

		return $return;
	}

	public function presentStartDate( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$return = $this->t->formatDate();
		return $return;
	}

	public function presentStartTime( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$return = $this->t->formatTime();
		return $return;
	}

	public function presentEndDate( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getEnd() );
		$return = $this->t->formatDate();
		return $return;
	}

	public function presentEndTime( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getEnd() );
		$return = $this->t->formatTime();
		return $return;
	}

	public function presentStatus( SH4_Shifts_Model $model )
	{
		$return = $model->isPublished() ? '__Published__' : '__Draft__';
		return $return;
	}

	public function export( SH4_Shifts_Model $model, $withContacts = FALSE )
	{
		$return = array();

		$return['id'] = $model->getId();

		$return['start_date'] = $this->presentStartDate( $model );
		$return['start_time'] = $this->presentStartTime( $model );
		$return['end_date'] = $this->presentEndDate( $model );
		$return['end_time'] = $this->presentEndTime( $model );

		$this->shiftsDuration->reset();
		$this->shiftsDuration->add( $model );
		$duration = $this->shiftsDuration->getDurationHours();
		$return['duration'] = $duration;

		$calendar = $model->getCalendar();
		$return['calendar'] = $calendar->getTitle();

		$employee = $model->getEmployee();
		$return['employee'] = $employee->getTitle();

		if( $withContacts ){
			$email = NULL;
			$user = $this->appQuery->findUserByEmployee( $employee );
			if( $user ){
				$email = $user->getEmail();
			}
			$return['employee_email'] = $email;
		}

		$return['status'] = $this->presentStatus( $model );

		// $return['start'] = $model->getStart();
		// $return['end'] = $model->getEnd();

		return $return;
	}
}