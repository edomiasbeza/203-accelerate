<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_Controller_Employee
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		HC3_Session $session,

		SH4_Shifts_Query $query,
		SH4_Shifts_Command $command,
		SH4_Employees_Query $employees
		)
	{
		$this->post = $post;
		$this->session = $session;

		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
		$this->employees = $hooks->wrap($employees);
	}

	public function execute( $ids, $employeeId )
	{
		$employee = $this->employees->findById( $employeeId );

		$ids = HC3_Functions::unglueArray( $ids );
		$shifts = $this->query->findManyById( $ids );
		foreach( $shifts as $shift ){
			$this->command->changeEmployee( $shift, $employee );
		}

		$to = 'schedule';

		$msg = $employeeId ? '__Employee Changed__' : '__Employee Unassigned__';

		$return = array( $to, $msg );
		return $return;
	}
}