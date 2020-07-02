<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Submit extends HC3_Ui_Abstract_Input
{
	// protected $el = 'input';
	protected $el = 'button';
	protected $uiType = 'input/submit';
	protected $formAction = NULL;

	public function __construct( $htmlFactory, $label, $name = NULL, $alt = NULL )
	{
		$this->htmlFactory = $htmlFactory;
		$this->label = $label;
		$this->alt = strlen($alt) ? $alt : $label; 
		$this->name = $name;
	}

	public function setFormAction( $set )
	{
		$this->formAction = $set;
		if( NULL === $this->name ){
			$this->name = 'hca';
			$this->addAttr( 'value', $set );
		}
		return $this;
	}

	public function getFormAction()
	{
		return $this->formAction;
	}

	public function render()
	{
		$this
			->addAttr('type', 'submit' )
			->addAttr('name', $this->htmlName() )
			->addAttr('title', $this->alt )
			// ->addAttr('value', $this->label, FALSE )
			;

		$this->setChild( 'label', $this->label );

		if( NULL !== $this->formAction ){
			$this
				->addAttr('formaction', $this->formAction )
				;
		}

		$out = parent::render();
		return $out;
	}
}