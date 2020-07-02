<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_View_Delete
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Shifts_View_Common $common,
		SH4_Shifts_Query $query
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->query = $hooks->wrap($query);
		$this->common = $hooks->wrap($common);
		$this->self = $hooks->wrap($this);
	}

	public function render( $id )
	{
		$model = $this->query->findById($id);

		$out = $this->ui->makeForm(
			'shifts/' . $id . '/delete',
			$this->ui->makeList()
				->gutter(2)
				->add( $this->ui->makeInputSubmit( '__Confirm Delete__')->tag('danger') )
			);

		$help = '__This operation cannot be undone.__';

		$out = $this->ui->makeList( array($help, $out) );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->common->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;

		$out = $this->layout->render();

		return $out;
	}

	public function header( $model )
	{
		$out = '__Delete__';
		return $out;
	}
}