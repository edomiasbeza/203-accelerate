<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Calendars_IModel
{
	public function isShift();
	public function isAvailability();
	public function isTimeoff();
	public function getType();
}

class SH4_Calendars_Model implements SH4_Calendars_IModel
{
	const STATUS_ACTIVE = 'publish';
	const STATUS_ARCHIVE = 'trash';

	const TYPE_SHIFT = 'shift';
	const TYPE_TIMEOFF = 'timeoff';
	const TYPE_AVAILABILITY = 'availability';

	private $id = NULL;
	private $title = NULL;
	private $status = self::STATUS_ACTIVE;
	private $color = NULL;
	private $description = NULL;
	private $type = 0;
	private $sortOrder = 1;

	public function __construct( $id, $title, $status = self::STATUS_ACTIVE, $color = '#cbe86b', $description = NULL, $type = self::TYPE_TIMEOFF, $sortOrder = 1  )
	{
		$this->id = $id;
		$this->title = $title;
		$this->color = $color;
		$this->status = $status;
		$this->description = $description;
		$this->type = $type;
		$this->sortOrder = $sortOrder;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getColor()
	{
		return $this->color;
	}

	public function getType()
	{
		return $this->type;
	}

	public function isShift()
	{
		return ( $this->type == self::TYPE_SHIFT );
	}

	public function isAvailability()
	{
		return ( $this->type == self::TYPE_AVAILABILITY );
	}

	public function isTimeoff()
	{
		return ( $this->type == self::TYPE_TIMEOFF );
	}

	public function isActive()
	{
		return ( $this->status == self::STATUS_ACTIVE );
	}

	public function isArchived()
	{
		return ( $this->status == self::STATUS_ARCHIVE );
	}

	public function getSortOrder()
	{
		return $this->sortOrder;
	}
}