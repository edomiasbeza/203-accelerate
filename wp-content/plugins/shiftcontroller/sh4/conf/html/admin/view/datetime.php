<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Conf_Html_Admin_View_Datetime
{
	protected $ui = NULL;
	protected $settings = NULL;

	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Time $t,

		HC3_Settings $settings
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;
		$this->t = $t;

		$this->settings = $hooks->wrap($settings);
		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$pnames = array(
			'datetime_date_format', 'datetime_time_format', 'datetime_week_starts', 'full_day_count_as',
			'skip_weekdays', 'datetime_timezone',
			'datetime_min_time', 'datetime_max_time', 'datetime_step',
			'datetime_hide_schedule_reports'
		);
		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}
		$inputs = array();

		$inputs[] = $this->ui->makeInputSelect(
			'datetime_date_format',
			'__Date Format__',
			array(
				'd/m/Y'	=> date('d/m/Y'),
				'd-m-Y'	=> date('d-m-Y'),
				'n/j/Y'	=> date('n/j/Y'),
				'Y/m/d'	=> date('Y/m/d'),
				'd.m.Y'	=> date('d.m.Y'),
				'j M Y'	=> date('j M Y'),
				'Y-m-d'	=> date('Y-m-d'),
				),
			$values['datetime_date_format']
			);

		$inputs[] = $this->ui->makeInputSelect(
			'datetime_time_format',
			'__Time Format__',
			array(
				'g:ia'	=> date('g:ia'),
				'g:i A'	=> date('g:i A'),
				'H:i'	=> date('H:i'),
				),
			$values['datetime_time_format']
			);

		$inputs[] = $this->ui->makeInputSelect(
			'datetime_week_starts',
			'__Week Starts On__',
			array(
				0	=> '__Sun__',
				1	=> '__Mon__',
				2	=> '__Tue__',
				3	=> '__Wed__',
				4	=> '__Thu__',
				5	=> '__Fri__',
				6	=> '__Sat__',
				),
			$values['datetime_week_starts']
			);

		$countAsOptionsValues = range( 1, 24 );
		$countAsOptionsKeys = array();
		foreach( $countAsOptionsValues as $v ){
			$countAsOptionsKeys[] = $v * (60 * 60);
		}
		$countAsOptions = array_combine( $countAsOptionsKeys, $countAsOptionsValues );

		$inputs[] = $this->ui->makeInputSelect(
			'full_day_count_as',
			'__Full Day Counts As__' . ' (' . '__Hours__' . ')',
			$countAsOptions,
			$values['full_day_count_as']
			);

		$inputs = $this->ui->makeGrid( $inputs );

		$moreInputs = array();

	// min/max time
		$moreInputs2 = array();
		$moreInputs2[] = $this->ui->makeInputTime(
			'datetime_min_time',
			'__Min Start Time__',
			$values['datetime_min_time'],
			'nolimit'
			);
		$moreInputs2[] = $this->ui->makeInputTime(
			'datetime_max_time',
			'__Max End Time__',
			$values['datetime_max_time'],
			'nolimit'
			);

		$stepOptions = array( 5*60 => 5, 10*60 => 10, 15*60 => 15, 20*60 => 20, 30*60 => 30, 60*60 => 60 );
		$moreInputs2[] = $this->ui->makeInputSelect(
			'datetime_step',
			'__Time Increment__' . ' (' . '__Minutes__' . ')',
			$stepOptions,
			$values['datetime_step']
			);

		$moreInputs2 = $this->ui->makeGrid( $moreInputs2 );
		$moreInputs[] = $moreInputs2;

	// timezone
		$timezoneOptions = $this->t->getTimezones();

		$defaultLabel = $this->t->getDefaultTimezone();
		$defaultLabel = ' - ' . '__Default Timezone__' . ' - ' . ' [' . $defaultLabel . ']';

		$timezoneOptions = array_merge( array('' => $defaultLabel), $timezoneOptions );

		$moreInputs[] = $this->ui->makeInputSelect(
			'datetime_timezone',
			'__Timezone__',
			$timezoneOptions,
			$values['datetime_timezone']
			);

		$this->t->setNow();
		$currentTimeView = $this->t->formatDateWithWeekday()  . ' ' . $this->t->formatTime();
		$currentTimeView = '<div class="hc-muted2">' . $currentTimeView . '</div>';

		$moreInputs[] = $currentTimeView;

	// disabled weekdays
		$disabledWeekdays = array();
		$weekdays = $this->t->getWeekdays();

		$currentDisabled = $this->settings->get( 'skip_weekdays', TRUE );
		foreach( $weekdays as $wkd => $wkdName ){
			$isChecked = in_array( $wkd, $currentDisabled ) ? TRUE : FALSE;
			$disabledWeekdays[] = $this->ui->makeInputCheckbox( 'skip_weekdays[]', $wkdName, $wkd, $isChecked );
		}
		$disabledWeekdays = $this->ui->makeListInline( $disabledWeekdays );
		$disabledWeekdays = $this->ui->makeLabelled( '__Days Of Week Disabled__', $disabledWeekdays );
		$moreInputs[] = $disabledWeekdays;

	// hide schedule reports
		$hideScheduleReports = $this->ui->makeInputCheckbox( 'datetime_hide_schedule_reports', '__Hide Hours Reports In Schedule View__', 1, $values['datetime_hide_schedule_reports'] );
		$moreInputs[] = $hideScheduleReports;

		$moreInputs = $this->ui->makeList( $moreInputs );

		$inputs = $this->ui->makeList( array($inputs, $moreInputs) );

		$out = $this->ui->makeForm(
			'admin/conf/datetime',
			$this->ui->makeList(
				array( $inputs, $this->ui->makeInputSubmit( '__Save__')->tag('primary') )
				)
			);

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			// ->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__Date and Time__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}