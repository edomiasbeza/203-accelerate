<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_Controller_Delete
{
	public function __construct(
		HC3_Hooks $hooks,

		SH4_Shifts_Query $query,
		SH4_Shifts_Command $command
		)
	{
		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );
		$this->command->delete( $model );

		// $to = '-referrer-';
		$to = 'schedule';
		$return = array( $to, '__Shift Deleted__' );
		return $return;
	}
}