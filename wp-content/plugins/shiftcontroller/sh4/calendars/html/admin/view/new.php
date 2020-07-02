<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Calendars_Html_Admin_View_New
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Enqueuer $enqueuer,

		SH4_Shifts_Availability $availability
	)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->layout = $layout;

		$this->availability = $hooks->wrap( $availability );

		$enqueuer->addScript('colorpicker',	'hc3/ui/element/input/colorpicker/assets/input.js');
	}

	public function render()
	{
		$form = $this->ui->makeForm(
			'admin/calendars/new',
			$this->self->form()
			);

		$this->layout
			->setContent( $form )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__Add New Calendar__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		return $return;
	}

	public function form()
	{
		$inputs = array();
		$inputs[] = $this->ui->makeInputText( 'title', '__Title__' )->bold();
		$inputs[] = $this->ui->makeInputRichTextarea( 'description', '__Description__' )->setRows(6);

		$typeOptions = array(
			SH4_Calendars_Model::TYPE_SHIFT			=> '__Shift__',
			SH4_Calendars_Model::TYPE_TIMEOFF		=> '__Timeoff__',
			// SH4_Calendars_Model::TYPE_AVAILABILITY	=> '__Availability__',
			);

		// if( ! $this->availability->hasAvailability() ){
			// $typeOptions[SH4_Calendars_Model::TYPE_AVAILABILITY] = '__Availability__';
		// }

		$typeInput = $this->ui->makeInputRadioSet( 'calendar_type', '__Type__', $typeOptions, SH4_Calendars_Model::TYPE_SHIFT );
		$inputs[] = $typeInput;

		$inputs[] = $this->ui->makeInputColorpicker( 'color', '__Color__' );

		$inputs = $this->ui->makeList( $inputs );

		$buttons = $this->ui->makeInputSubmit( '__Add New Calendar__')
			->tag('primary')
			;

		$out = $this->ui->makeList( array($inputs, $buttons) );

		return $out;
	}
}