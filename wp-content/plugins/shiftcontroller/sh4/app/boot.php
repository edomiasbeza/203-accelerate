<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_App_Boot
{
	public function __construct(
		HC3_Dic $dic,
		HC3_Settings $settings,
		HC3_Ui_Topmenu $topmenu,
		HC3_Router $router,
		HC3_Acl $acl,

		SH4_App_Migration $migration
		)
	{
		$migration->up();

		$dic->bind( 'SH4_Permission', 'HC3_IPermission' );

		$settings
			->init( 'datetime_date_format', 'j M Y' )
			->init( 'datetime_time_format', 'g:ia' )
			->init( 'datetime_week_starts', 0 )
			->init( 'datetime_timezone', '' )
			->init( 'full_day_count_as', 8*60*60 )

			->init( 'skip_weekdays', array() )

			->init( 'datetime_min_time', 0 )
			->init( 'datetime_max_time', 24*60*60 )
			->init( 'datetime_step', 5*60 )

			->init( 'datetime_hide_schedule_reports', 0 )
			->init( 'conflicts_calendar_only', 0 )
			->init( 'shifts_no_draft', 0 )
			;

		$topmenu
			->addAfter( NULL, 'profile', array('user/profile', '__Profile__') )
			->addBefore( 'profile', 'admin', array('admin', '__Administration__') )
			;

		$router
			->register( 'get:admin', array('SH4_App_Html_View_Admin', 'render') )

			->register( 'get:admin/about', array('SH4_App_Html_View_Admin_About', 'render') )

			->register( 'post:admin/reinstall', array('SH4_App_Html_Controller_Admin_Reinstall', 'execute') )
			->register( 'post:admin/reinstall/shifts', array('SH4_App_Html_Controller_Admin_Reinstall', 'executeShifts') )
			;

		$acl
			->register( 'get:admin', array('SH4_App_Acl', 'checkAdmin') )
			->register( 'get:admin/{anything}', array('SH4_App_Acl', 'checkAdmin') )
			->register( 'post:admin/{anything}', array('SH4_App_Acl', 'checkAdmin') )
			->register( 'get:admin/{anything}/{anything}', array('SH4_App_Acl', 'checkAdmin') )
			->register( 'post:admin/{anything}/{anything}', array('SH4_App_Acl', 'checkAdmin') )

			->register( 'get:manager', array('SH4_App_Acl', 'checkManager') )
			->register( 'get:manager/{anything}', array('SH4_App_Acl', 'checkManager') )
			->register( 'post:manager/{anything}', array('SH4_App_Acl', 'checkManager') )
			->register( 'get:manager/{anything}/{anything}', array('SH4_App_Acl', 'checkManager') )
			->register( 'post:manager/{anything}/{anything}', array('SH4_App_Acl', 'checkManager') )

			->register( 'get:employee', array('SH4_App_Acl', 'checkEmployee') )
			->register( 'get:employee/{anything}', array('SH4_App_Acl', 'checkEmployee') )
			->register( 'post:employee/{anything}', array('SH4_App_Acl', 'checkEmployee') )
			->register( 'get:employee/{anything}/{anything}', array('SH4_App_Acl', 'checkEmployee') )
			->register( 'post:employee/{anything}/{anything}', array('SH4_App_Acl', 'checkEmployee') )

			->register( 'get:user', array('SH4_App_Acl', 'checkUser') )
			->register( 'get:user/{anything}', array('SH4_App_Acl', 'checkUser') )
			->register( 'post:user/{anything}', array('SH4_App_Acl', 'checkUser') )
			->register( 'get:user/{anything}/{anything}', array('SH4_App_Acl', 'checkUser') )
			->register( 'post:user/{anything}/{anything}', array('SH4_App_Acl', 'checkUser') )
			;
	}
}