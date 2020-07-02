<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Table extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $striped = TRUE;
	protected $bordered = FALSE;
	protected $segments = array();
	protected $labelled = FALSE;

	public function __construct( HC3_Ui $htmlFactory, $header = NULL, $rows = array(), $striped = TRUE )
	{
		$this->htmlFactory = $htmlFactory;
		$this->striped = $striped;

		$final_rows = array();

		if( NULL === $header ){
			$header = $rows[0];
			foreach( array_keys($header) as $k ){
				$header[$k] = NULL;
			}
		}

		$final_rows[] = $this->htmlFactory->makeCollection($header);
		foreach( $rows as $row ){
			$final_row = $this->htmlFactory->makeCollection($row);
			$final_rows[] = $final_row;
		}

		parent::__construct( $final_rows );
	}

	public function setBordered( $set = TRUE )
	{
		$this->bordered = $set;
		return $this;
	}

	public function setLabelled( $set = TRUE )
	{
		$this->labelled = $set;
		return $this;
	}

	public function setSegments( array $set )
	{
		$this->segments = $set;
		return $this;
	}

	public function setStriped( $striped = TRUE )
	{
		$this->striped = $striped;
		return $this;
	}

	public function render()
	{
	// header
		$show = array();

		$rows = $this->getChildren();
		$header = array_shift( $rows );
		$header = $header->getChildren();

	// if all null then we don't need header
		$show_header = FALSE;
		foreach( $header as $k => $hv ){
			if( $hv !== NULL ){
				$show_header = TRUE;
				break;
			}
		}

		if( $this->labelled ){
			$width = 'x-' . (count($header) - 1);
			$firstWidth = 'x-x';
		}
		else {
			$width = '1-' . count($header);
			$firstWidth = $width;
		}

		// header
		$trHeader = NULL;
		if( $show_header ){
			$grid = array();

			$ii = 0;
			foreach( $header as $k => $hv ){
				$cell = NULL;

				$cell = $this->htmlFactory->makeBlock( $hv );
				if( $this->gutter ){
					$cell
						->addAttr('class', 'hc-p' . $this->gutter)
						->addAttr('class', 'hc-px' . $this->gutter . '-xs')
						;
				}
				else {
					$cell
						->addAttr('class', 'hc-my1')
						;
				}
				$cell
					->addAttr('class', 'hc-py1-xs')
					->addAttr('class', 'hc-xs-hide')
					;

				if( $ii ){
					$grid[] = array( $cell, $width, 12 );
				}
				else {
					$grid[] = array( $cell, $firstWidth, 12 );
				}
				$ii++;
			}

			$trHeader = $this->htmlFactory->makeGrid( $grid )->gutter(0);

			if( $this->bordered ){
				// $trHeader->setBordered( $this->bordered );
			}
			if( $this->segments ){
				$trHeader->setSegments( $this->segments );
			}

			if( ! $this->bordered ){
				$trHeader = $this->htmlFactory->makeBlock($trHeader)
					->addAttr('class', 'hc-xs-hide')
					->addAttr('class', 'hc-fs4')
					->addAttr('style', 'line-height: 1.5em;')
					->addAttr('class', 'hc-border-bottom')
					;
			}

			if( $this->striped ){
				if( defined('WPINC') ){
					$trHeader->addAttr('class', 'hc-bg-white');
				}
			}

			$trHeader = $trHeader->render();
// echo $trHeader;
// exit;
			if( is_object($trHeader) && method_exists($trHeader, 'addAttr') ){
				$trHeader
					->addAttr('class', 'hc-xs-hide')
					;
			}

			// $show[] = $trHeader;
		}

	// rows
		$rri = 0;

		foreach( $rows as $rid => $row ){
			$row = $row->getChildren();

			$rri++;
			$row_cells = array();

			$ii = 0;
			$grid = array();
			reset( $header );
			foreach( $header as $k => $hv ){
				$v = array_key_exists($k, $row) ? $row[$k] : NULL;
				$cell = $this->htmlFactory->makeBlock( $v );

				if( $this->gutter ){
					$cell
						->addAttr('class', 'hc-p' . $this->gutter)
						->addAttr('class', 'hc-px' . $this->gutter . '-xs')
						;
				}
				$cell
					->addAttr('class', 'hc-py1-xs')
					;

				if( strlen($hv) ){
					$cell_header = $this->htmlFactory->makeBlock($hv)
						->addAttr('class', 'hc-fs1')
						->addAttr('class', 'hc-muted2')
						->addAttr('class', 'hc-lg-hide')
						->addAttr('class', 'hc-p1-xs')
						;
					$cell = $this->htmlFactory->makeCollection( array($cell_header, $cell) );
				}

				if( $ii ){
					$grid[] = array( $cell, $width, 12 );
				}
				else {
					$grid[] = array( $cell, $firstWidth, 12 );
				}
				$ii++;
			}

			$tr = $this->htmlFactory->makeGrid( $grid )->gutter(0);
			if( $this->bordered ){
				$tr->setBordered( $this->bordered );
			}
			if( $this->segments ){
				$tr->setSegments( $this->segments );
			}

			if( $this->striped ){
				$tr = $this->htmlFactory->makeBlock( $tr );
				if( defined('WPINC') ){
					if( $rri % 2 ){
						$tr->addAttr('class', 'hc-bg-wpsilver');
					}
					else {
						$tr->addAttr('class', 'hc-bg-white');
					}
				}
				else {
					if( $rri % 2 ){
						$tr->addAttr('class', 'hc-bg-lightsilver');
					}
				}
			}

			$show[] = $tr;
		}

		if( $this->bordered ){
			$out = $this->htmlFactory->makeCollection( $show );
			$out = $this->htmlFactory->makeBlock( $out )
				->addAttr('class', 'hc-table')
				// ->addAttr('style', 'border: red 1px solid;')
				;
		}
		else {
			$out = $this->htmlFactory->makeList( $show )->gutter(0);
			$out = $this->htmlFactory->makeBlock( $out )
				->addAttr('class', 'hc-border')
				;
		}

		if( $trHeader ){
			$trHeader = $this->htmlFactory->makeBlock( $trHeader )
				->addAttr('class', 'hc-full-width')
				->addAttr('class', 'hc-table-header')
				->addAttr('class', 'hc-bg-white')
				;

			if( defined('WPINC') && is_admin() ){
				$trHeader
					->addAttr('class', 'hc-table-header-wpadmin')
					;
			}

			if( $this->bordered ){
				$trHeader
					->addAttr('class', 'hc-border')
					;

				if( $this->bordered !== TRUE ){
					$trHeader
						->addAttr('class', 'hc-border-'. $this->bordered)
						;
				}
			}

			$out = $this->htmlFactory->makeCollection( array($trHeader, $out) );
		}

		return $out;
	}
}