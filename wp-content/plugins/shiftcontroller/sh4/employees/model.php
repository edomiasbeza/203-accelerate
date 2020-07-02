<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Employees_Model
{
	const STATUS_ACTIVE = 'publish';
	const STATUS_ARCHIVE = 'trash';

	private $id = NULL;
	private $title = NULL;
	private $description = NULL;
	private $status = self::STATUS_ACTIVE;
	private $sortOrder = 1;

	public function __construct( $id, $title, $description = NULL, $status = self::STATUS_ACTIVE, $sortOrder = 1 )
	{
		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
		$this->status = $status;
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

	public function isActive()
	{
		return ($this->status == self::STATUS_ACTIVE);
	}

	public function isArchived()
	{
		return ($this->status == self::STATUS_ARCHIVE);
	}

	public function getSortOrder()
	{
		return $this->sortOrder;
	}
}