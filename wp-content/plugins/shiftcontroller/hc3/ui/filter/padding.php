<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Padding
{
	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}

	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('padding', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$padding = $tags['padding'];
		if( ! is_array($padding) ){
			$padding = array( $padding );
		}

		foreach( $padding as $p ){
			$element->addAttr('class', 'hc-p' . $p);
		}

		return $element;
	}
}