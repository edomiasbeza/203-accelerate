<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Calendars_Html_Admin_Controller_New
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Calendars_Command $command
		)
	{
		$this->post = $hooks->wrap($post);
		$this->command = $hooks->wrap($command);
	}

	public function execute()
	{
		$title = $this->post->get('title');
		$color = $this->post->get('color');
		$description = $this->post->get('description');
		$type = $this->post->get('calendar_type');

		$newId = $this->command->create( $title, $color, $description, $type );

		$to = 'admin/calendars/' . $newId . '/shifttypes/new';
		$return = array( $to, array('__New Calendar Added__') );
		return $return;
	}
}