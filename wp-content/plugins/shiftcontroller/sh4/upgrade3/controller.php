<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Upgrade3_Controller
{
	public function __construct(
		HC3_Hooks $hooks,
		SH4_Upgrade3_Command $upgradeCommand
		)
	{
		$this->upgradeCommand = $hooks->wrap( $upgradeCommand );
		$this->self = $hooks->wrap($this);
	}

	public function execute()
	{
		global $wpdb;
		$prfx = $wpdb->prefix . 'shiftcontroller_v3_';

		$sql = "SELECT * FROM {$prfx}locations";
		$oldLocations = $wpdb->get_results( $sql, ARRAY_A );

		$level = 1;
		$sql = "SELECT * FROM {$prfx}users WHERE level & $level";
		$oldEmployees = $wpdb->get_results( $sql, ARRAY_A );

		$sql = "SELECT * FROM {$prfx}shifts";
		$oldShifts = $wpdb->get_results( $sql, ARRAY_A );

		$this->upgradeCommand->upgrade( $oldLocations, $oldEmployees, $oldShifts );

		return array('schedule', '__Upgraded__');
	}
}