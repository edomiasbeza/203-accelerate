<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Boot
{
	public function __construct(
		HC3_Dic $dic,
		HC3_Hooks $hooks,
		HC3_Ui_Topmenu $topmenu,
		HC3_Router $router,
		HC3_Acl $acl
	)
	{
		$dic->bind( 'SH4_Permission', 'HC3_IPermission' );

		$acl
			->register( 'get:myschedule', array('SH4_Schedule_Acl', 'checkMy') )
			;
		$scheduleParams = array('type' => NULL, 'groupby' => NULL, 'start' => NULL, 'end' => NULL);

		$everyoneLabel = '__Schedule__';
		$toCheck = 'myschedule';
		if( $acl->check('get:' . $toCheck) ){
			$everyoneLabel = '__Everyone Schedule__';
		}

		$topmenu
			->addBefore( NULL, 'myschedule', array( array('myschedule', $scheduleParams), '__My Schedule__') )
			->addBefore( NULL, 'schedule', array( array('schedule', $scheduleParams), $everyoneLabel) )
			;

		$router
			->register( 'get:schedule', array('SH4_Schedule_Html_View_Index', 'render') )
			->register( 'get:myschedule', array('SH4_Schedule_Html_View_Index', 'renderMy') )

			->register( 'post:schedule/dates', array('SH4_Schedule_Html_Controller_Dates', 'execute') )
			->register( 'post:schedule/exacttime', array('SH4_Schedule_Html_Controller_ExactTime', 'execute') )
			;

		$acl
			->register( 'get:myschedule', array('SH4_Schedule_Acl', 'checkMy') )
			;
	}
}