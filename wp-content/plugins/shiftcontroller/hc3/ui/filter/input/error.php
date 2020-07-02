<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Input_Error
{
	public function __construct( 
		HC3_Session $session,
		HC3_Ui $ui
		)
	{
		$this->session = $session;
		$this->ui = $ui;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( substr($uiType, 0, strlen('input/')) != 'input/' ){
			return $element;
		}

		$errors = $this->session->getFlashdata('form_errors');
		$name = $element->name();

		if( is_array($errors) && array_key_exists($name, $errors) ){
			$error = $errors[$name];
			$error = $this->ui->makeBlock( $error )
				->paddingY(2)
				->addAttr('class', 'hc-red')
				->addAttr('class', 'hc-border-top')
				->addAttr('class', 'hc-border-red')
				;

			$element = $this->ui->makeList( array($element, $error) );
		}

		return $element;
	}
}