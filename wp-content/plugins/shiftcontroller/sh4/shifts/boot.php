<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_Boot
{
	public function __construct(
		SH4_Shifts_Migration $migration,
		SH4_Shifts_Conflicts $conflicts,
		HC3_Router $router,
		HC3_Acl $acl
	)
	{
		$migration->up();

		$conflicts
			->register( 'SH4_Shifts_Conflict_Overlap' )
			->register( 'SH4_Shifts_Conflict_Holidays' )
			;

		$router
			->register( 'get:shifts/{id}/delete', array('SH4_Shifts_View_Delete', 'render') )
			->register( 'post:shifts/{id}/delete', array('SH4_Shifts_Controller_Delete', 'execute') )

			->register( 'post:shifts/{id}/publish', array('SH4_Shifts_Controller_Publish', 'execute') )
			->register( 'post:shifts/{id}/unpublish', array('SH4_Shifts_Controller_Unpublish', 'execute') )

			->register( 'get:shifts/{id}/employee', array('SH4_Shifts_View_Employee', 'render') )
			->register( 'post:shifts/{id}/employee/{employee}', array('SH4_Shifts_Controller_Employee', 'execute') )

			->register( 'get:shifts/{id}/time', array('SH4_Shifts_View_Time', 'render') )
			->register( 'post:shifts/{id}/time', array('SH4_Shifts_Controller_Time', 'execute') )

			->register( 'get:shifts/{id}/conflicts', array('SH4_Shifts_View_Conflicts', 'render') )
			->register( 'get:shifts/{id}', array('SH4_Shifts_View_Zoom', 'render') )
			;

		$acl
			->register( 'get:shifts/{id}/delete', array('SH4_Shifts_Acl', 'checkDelete') )
			->register( 'post:shifts/{id}/delete', array('SH4_Shifts_Acl', 'checkDelete') )

			->register( 'post:shifts/{id}/publish', array('SH4_Shifts_Acl', 'checkCreatePublished') )
			->register( 'post:shifts/{id}/unpublish', array('SH4_Shifts_Acl', 'checkCreatePublished') )

			->register( 'get:shifts/{id}/employee', array('SH4_Shifts_Acl', 'checkManager') )
			->register( 'post:shifts/{id}/employee/{employee}', array('SH4_Shifts_Acl', 'checkEmployeeAssignment') )

			->register( 'get:shifts/{id}/time', array('SH4_Shifts_Acl', 'checkChangeTime') )
			->register( 'post:shifts/{id}/time/{start}/{end}', array('SH4_Shifts_Acl', 'checkChangeTime') )

			->register( 'get:shifts/{id}', array('SH4_Shifts_Acl', 'checkView') )
			;
	}
}