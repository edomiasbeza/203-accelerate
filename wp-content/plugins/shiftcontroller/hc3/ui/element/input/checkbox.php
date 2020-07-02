<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Checkbox extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/checkbox';

	protected $checked = FALSE;
	protected $readonly = FALSE;

	protected $asList = FALSE;

	public function __construct( $htmlFactory, $name, $label = NULL, $value = NULL, $checked = FALSE )
	{
		parent::__construct( $htmlFactory, $name, $label, $value );
		$this->value = $value;
		$this->checked = $checked;
	}

	public function setValue( $value )
	{
		$this->checked = $value ? TRUE : FALSE;
		$this->value = $value;
		return $this;
	}

	public function setChecked( $set = TRUE )
	{
		$this->checked = $set;
		return $this;
	}

	public function setReadonly( $set = TRUE )
	{
		$this->readonly = $set;
		return $this;
	}

	public function asList( $set = TRUE )
	{
		$this->asList = $set;
		return $this;
	}

	public function render()
	{
		$out = $this->htmlFactory->makeElement('input')
			->addAttr('type', 'checkbox' )
			->addAttr('name', $this->htmlName() )
			// ->addAttr('class', 'hc-field')
			;

		if( strlen($this->value) ){
			$out->addAttr('value', $this->value);
		}

		if( $this->checked ){
			$out->addAttr('checked', 'checked');
		}

		if( $this->readonly ){
			$out
				->addAttr('readonly', 'readonly')
				->addAttr('disabled', 'disabled')
				;
		}

		$attr = $this->getAttr();
		foreach( $attr as $k => $v ){
			$out->addAttr($k, $v);
		}

		if( strlen($this->label) ){
			$htmlId = 'hc3_' . mt_rand( 100000, 999999 );

			$out->addAttr('id', $htmlId);
			$label = $this->htmlFactory->makeElement('label', $this->label)
				->addAttr('for', $htmlId )
				// ->addAttr('class', 'hc-fs2')
				;

			if( $this->asList ){
				$out = $this->htmlFactory->makeList( array($out, $label) )->gutter(0);
				$out = $this->htmlFactory->makeBlock( $out )
					->addAttr('class', 'hc-align-center')
					;
			}
			else {
				$out = $this->htmlFactory->makeListInline( array($out, $label) )->gutter(1);
				$out = $this->htmlFactory->makeBlock( $out )
					->addAttr('class', 'hc-nowrap')
					;
			}
		}

		return $out;
	}
}