<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Calendars_Presenter
{
	public function __construct( HC3_Ui $ui )
	{
		$this->ui = $ui;
	}

	public function presentDescription( SH4_Calendars_Model $calendar )
	{
		$return = $calendar->getDescription();

		if( defined('WPINC') ){
			$return = do_shortcode( $return );
		}

		return $return;
	}

	public function presentTitle( SH4_Calendars_Model $calendar )
	{
		$return = $calendar->getTitle();
		$return = $this->ui->makeBlockInline( $return )
			->paddingX(1)
			->tag('bgcolor', $calendar->getColor() )
			->tag('color', 'white')
			;

		if( $calendar->isArchived() ){
			$return
				->tag('font-style', 'line-through')
				;
		}

		if( $calendar->isTimeoff() ){
			$label = '(' . '__Time Off__' . ')';
			$return = $this->ui->makeListInline( array($return, $label) );
		}

		if( $calendar->isAvailability() ){
			$label = '(' . '__Availability__' . ')';
			$return = $this->ui->makeListInline( array($return, $label) );
		}

		return $return;
	}
}