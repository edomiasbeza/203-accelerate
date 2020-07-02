<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Platform_Boot
{
	public function __construct(
		HC3_Settings $settings,
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$defaultEmail = 'info@' . $_SERVER['SERVER_NAME'];

		$settings
			->init( 'email_from', $defaultEmail )
			->init( 'email_fromname', 'ShiftController' )
			->init( 'email_html', '1' )
			;

		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $return ){
				$return['admin/email'] = array( 'admin/conf/email', '__Email__' );
				$return['admin/publish'] = array( 'admin/publish', '__Publish__' );
				return $return;
				})
			;

		$router
			->register( 'get:admin/conf/email', array('SH4_Conf_Html_Admin_View_Email', 'render') )
			->register( 'post:admin/conf/email', array('SH4_Conf_Html_Admin_Controller_Email', 'execute') )
			->register( 'get:admin/publish', array('SH4_Conf_Html_Admin_View_Publish', 'render') )
			;
	}
}