<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Employees_IPresenter
{
	public function presentTitle( SH4_Employees_Model $employee );
}

class SH4_Employees_Presenter implements SH4_Employees_IPresenter
{
	public function __construct( HC3_Ui $ui )
	{
		$this->ui = $ui;
	}

	public function presentDescription( SH4_Employees_Model $employee )
	{
		$return = $employee->getDescription();

		// if( defined('WPINC') ){
			// $return = do_shortcode( $return );
		// }

		return $return;
	}

	public function presentTitle( SH4_Employees_Model $employee )
	{
		$return = $employee->getTitle();
		return $return;
	}
}