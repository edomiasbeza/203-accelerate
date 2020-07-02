<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_Controller_Dates
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post
		)
	{
		$this->post = $hooks->wrap($post);
	}

	public function execute()
	{
		$params = array();

		$start = $this->post->get('start');
		$params['start'] = $start;

		$end = $this->post->get('end');
		if( $end ){
			$params['end'] = $end;
		}

		$params['time'] = NULL;

		$return = array( array('-referrer-', $params), NULL );
		return $return;
	}
}