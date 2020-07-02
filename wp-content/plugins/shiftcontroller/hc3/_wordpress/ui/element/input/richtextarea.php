<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_RichTextarea extends HC3_Ui_Element_Input_Textarea
{
	protected $el = 'input';
	protected $uiType = 'input/text';
	protected $bold = FALSE;

	public function bold( $set = TRUE )
	{
		$this->bold = $set;
		return $this;
	}

	public function render()
	{
		if( ! is_admin() ){
			return parent::render();
		}

		$wpEditorSettings = array();
		$wpEditorSettings['textarea_name'] = $this->htmlName();

		$rows = $this->getAttr('rows');
		if( $rows ){
			$wpEditorSettings['textarea_rows'] = $rows;
		}

		// stupid wp, it outputs it right away
		ob_start();

		$editorId = $this->htmlId();
		wp_editor(
			$this->value,
			$editorId,
			$wpEditorSettings
			);

		if( 0 )
		{
			$more_js = <<<EOT
<script type="text/javascript">
var str = nts_tinyMCEPreInit.replace(/nts_wp_editor/gi, '$editor_id');
var ajax_tinymce_init = JSON.parse(str);

tinymce.init( ajax_tinymce_init.mceInit['$editor_id'] );
</script>
EOT;

//				_WP_Editors::enqueue_scripts();
//				print_footer_scripts();
//				_WP_Editors::editor_js();
			echo $more_js;
		}

		_WP_Editors::enqueue_scripts();
		_WP_Editors::editor_js();

		$out = ob_get_clean();

		if( strlen($this->label) ){
			$out = $this->htmlFactory->makeLabelled( $this->label, $out, $this->htmlId() );
		}

		return $out;
	}
}