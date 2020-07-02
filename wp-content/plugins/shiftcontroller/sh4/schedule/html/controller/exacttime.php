<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_Controller_ExactTime
{
	public function __construct(
		HC3_Time $t,
		HC3_Hooks $hooks,
		HC3_Post $post
		)
	{
		$this->t = $t;
		$this->post = $hooks->wrap($post);
	}

	public function execute()
	{
		$params = array();

		$date = $this->post->get('date');
		$time = $this->post->get('time');

		$dateTimeDb = $this->t->setDateDb( $date )
			->modify( '+' . $time . ' seconds' )
			->formatDateTimeDb()
		;

		$params['start'] = NULL;
		$params['end'] = NULL;
		$params['time'] = $dateTimeDb;


		$return = array( array('-referrer-', $params), NULL );
		return $return;
	}
}