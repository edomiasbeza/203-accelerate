<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Button extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/button';

	public function __construct( $label, $name = NULL, $alt = NULL )
	{
		$this->label = $label;
		$this->alt = strlen($alt) ? $alt : $label; 
		$this->name = $name;
	}

	public function render()
	{
		$this
			->addAttr('type', 'button' )
			->addAttr('name', $this->htmlName() )
			->addAttr('title', $this->alt )
			->addAttr('value', $this->label )
			;

		$out = parent::render();
		return $out;
	}
}