<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_IConflicts
{
	public function register( $check );
	public function get( SH4_Shifts_Model $shift );
}

class SH4_Shifts_Conflicts implements SH4_Shifts_IConflicts
{
	protected $checks = array();

	public function __construct( HC3_Dic $dic )
	{
		$this->dic = $dic;
	}

	public function register( $check )
	{
		$this->checks[] = $check;
		return $this;
	}

	public function get( SH4_Shifts_Model $shift )
	{
		$return = array();

		reset( $this->checks );
		foreach( $this->checks as $check ){
			$check = $this->dic->make( $check );

			$checkResult = $check->check( $shift );
			if( ! $checkResult ){
				$return[] = $check;
			}
		}

		return $return;
	}
}