<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
abstract class HC3_Ui_Abstract_Input extends HC3_Ui_Abstract_Element
{
	protected $htmlFactory = NULL;
	protected $_prefix = 'hc-';
	protected $name = NULL;
	protected $label = NULL;
	protected $value = NULL;

	protected $htmlId = NULL;

	public function __construct( $htmlFactory, $name, $label = NULL, $value = NULL )
	{
		static $useCount = 1;

		$this->htmlFactory = $htmlFactory;
		$this->name = $name;
		$this->label = $label;
		$this->setValue( $value );

		$this->htmlId = ($useCount > 1) ? $this->name . $useCount  : $this->name;
		$useCount++;
	}

	public function setValue( $value )
	{
		$this->value = $value;
		return $this;
	}

	public function htmlId()
	{
		return $this->htmlId;
	}

	public function htmlName()
	{
		return $this->_prefix . $this->name;
	}

	public function name()
	{
		return $this->name;
	}
}