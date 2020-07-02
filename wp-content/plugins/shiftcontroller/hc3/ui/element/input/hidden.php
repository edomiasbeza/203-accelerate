<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Hidden extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/hidden';

	public function __construct( $htmlFactory, $name, $value = NULL )
	{
		$this->htmlFactory = $htmlFactory;
		$this->name = $name;
		$this->setValue( $value );
	}

	public function render()
	{
		$out = $this->htmlFactory->makeElement('input')
			->addAttr('type', 'hidden' )
			->addAttr('name', $this->htmlName() )
			;

		if( strlen($this->value) ){
			$out->addAttr('value', $this->value);
		}

		return $out;
	}
}