<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Labelled extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $label = NULL;
	protected $content = NULL;

	public function __construct( HC3_Ui $html, $label, $content, $labelFor = NULL )
	{
		$this->htmlFactory = $html;
		$this->label = $label;
		$this->content = $content;
		$this->labelFor = $labelFor;

		$this->add( $this->label );
		$this->add( $this->content );
	}

	public function renderFieldset()
	{
		$label = $this->htmlFactory->makeElement( 'legend', $this->label );
		$content = $this->htmlFactory->makeCollection( array($label, $this->content) );

		$out = $this->htmlFactory->makeElement( 'fieldset', $content )
			;

		return $out;
	}

	public function render()
	{
		$label = $this->htmlFactory->makeElement('label', $this->label)
			// ->addAttr('class', 'hc-fs2')
			;

		if( strlen($this->labelFor) ){
			$label
				->addAttr('for', $this->labelFor )
				;
		}

		$out = $this->htmlFactory->makeList( array($label, $this->content) )
			->gutter(1)
			;

		return $out;
	}
}