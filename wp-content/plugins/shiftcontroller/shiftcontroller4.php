<?php
/*
 * Plugin Name: ShiftController
 * Plugin URI: http://www.shiftcontroller.com/
 * Description: Staff scheduling plugin
 * Version: 4.7.5
 * Author: plainware.com
 * Author URI: http://www.shiftcontroller.com/
 * Text Domain: shiftcontroller
 * Domain Path: /languages/
*/

define( 'SH4_VERSION', 475 );

if (! defined('ABSPATH')) exit; // Exit if accessed directly

if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices',
		create_function( '',
			"echo '<div class=\"error\"><p>" .
			__('ShiftController requires PHP 5.3 to function properly. Please upgrade PHP or deactivate ShiftController.', 'shiftcontroller') ."</p></div>';"
			)
	);
	return;
}

if( file_exists(dirname(__FILE__) . '/config.php') ){
	$conf = include( dirname(__FILE__) . '/config.php' );
}

$hc3path = defined('HC3_DEV_INSTALL') ? HC3_DEV_INSTALL : dirname(__FILE__) . '/hc3';
include_once( $hc3path . '/_wordpress/abstract/plugin.php' );

class ShiftController4 extends HC3_Abstract_Plugin
{
	public function __construct()
	{
		// $this->slug = 'shiftcontroller';
		$this->translate = 'shiftcontroller';
		$this->slug = 'shiftcontroller4';
		$this->label = 'Shift Controller';
		$this->prfx = 'sh4';
		$this->menuIcon = 'dashicons-calendar';
		// $this->requireCap = 'manage_options';

		$this->modules = array(
			'conf',
			'schedule',
			'employees',
			'calendars',
			'shifttypes',
			'shifts',
			'conflicts',
			'new',
			'users',
			'notifications',
			'ical',
			'feed',
			'app',
			'upgrade3',
			'platform',
			'promo',

// 'repeat',
// 'bulk'
// 'pickup'
			);

if( defined('HC3_DEV_INSTALL') ){
	$this->modules[] = 'demo';
}


		parent::__construct( __FILE__ );

		add_action(	'init', array($this, 'addRoles') );
		add_shortcode( 'shiftcontroller4', array($this, 'shortcode') );
	}

	public function addRoles()
	{
		$adminRole = 'sh4_admin';
		$r = get_role( $adminRole );
		if( $r ){
			return;
		}

		add_role(
			$adminRole,
			'ShiftController Administrator',
			array(
				'read' => TRUE,
				)
			);

		$capabilities = array(
			'manage_sh4',
		);

		global $wp_roles;
		foreach( $capabilities as $cap ){
			$wp_roles->add_cap( $adminRole, $cap );
			$wp_roles->add_cap( 'editor', $cap );
			$wp_roles->add_cap( 'administrator', $cap );
		}
	}

	public function shortcode( $shortcodeAtts )
	{
		if( is_admin() OR hc_is_rest() ){
			$return = 'ShiftController shortcode is rendered in front end only.';
			return $return;
		}

		$route = 'schedule';

		if( $shortcodeAtts && is_array($shortcodeAtts) ){
			$root = $this->root();
			$request = $root->make('HC3_Request');
			$slug = $request->getSlug();

			$processParams = array( 'type', 'groupby', 'start', 'end', 'time' );
			if( $slug != 'myschedule' ){
				$processParams = array_merge( $processParams, array('calendar', 'employee', 'hideui') );
			}
			$arrayFor = array( 'calendar', 'employee', 'hideui' );

			$allowedRoutes = array('schedule', 'myschedule');

			foreach( $shortcodeAtts as $k => $v ){
				if( $k == 'route' ){
					if( in_array($v, $allowedRoutes) ){
						$route = $v;
					}
				}

				if( ! in_array($k, $processParams) ){
					continue;
				}

				if( in_array($k, $arrayFor) ){
					if( strpos($v, ',') ){
						$v = explode(',', $v);
						for( $ii = 0; $ii < count($v); $ii++ ){
							$v[$ii] = trim( $v[$ii] );
						}
					}
					else {
						$v = array($v);
					}
				}
				$request->initParam( $k, $v );
			}
		}

		$this->actionResult = $this->handleRequest( $route );

		ob_start();
		echo $this->render();
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function handleRequest( $defaultSlug = '' )
	{
		if( ! $defaultSlug ){
			$defaultSlug = 'schedule';
		}

		$return = parent::handleRequest( $defaultSlug );

		$root = $this->root();
		$enqueuer = $root->make('HC3_Enqueuer');
		$enqueuer
			->addScript('sh4', 'sh4/app/assets/js/sh4.js')
			;

		return $return;
	}
}

$hcsh4 = new ShiftController4();

if (!function_exists('hc_is_rest')) {
	/**
	* Checks if the current request is a WP REST API request.
	* 
	* Case #1: After WP_REST_Request initialisation
	* Case #2: Support "plain" permalink settings
	* Case #3: URL Path begins with wp-json/ (your REST prefix)
	*          Also supports WP installations in subfolders
	* 
	* @returns boolean
	* @author matzeeable
	*/
	function hc_is_rest() {
	  $prefix = rest_get_url_prefix( );
	  if (defined('REST_REQUEST') && REST_REQUEST // (#1)
			|| isset($_GET['rest_route']) // (#2)
				 && strpos( trim( $_GET['rest_route'], '\\/' ), $prefix , 0 ) === 0)
			return true;

	  // (#3)
	  $rest_url = wp_parse_url( site_url( $prefix ) );
	  $current_url = wp_parse_url( add_query_arg( array( ) ) );
	  return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}
}