<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Grid extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $widths = array();
	protected $bordered = FALSE;
	protected $segments = array();

	public function __construct( HC3_Ui $htmlFactory, array $items = array(), $itemWidth = NULL )
	{
		$this->htmlFactory = $htmlFactory;

		$count = count($items);

		for( $i = 0; $i < $count; $i++ ){
			if( ! is_array($items[$i]) ){
				$items[$i] = array( $items[$i] );
			}
			if( ! isset($items[$i][1]) ){ // desktop width
				$thisItemWidth = ( NULL == $itemWidth ) ? '1-' . $count : (12 / $itemWidth);
				$items[$i][1] = $thisItemWidth;
			}
			if( ! isset($items[$i][2]) ){ // mobile width
				$items[$i][2] = 12;
			}

			$this->add( $items[$i][0], $items[$i][1], $items[$i][2] );
		}
	}

	public function setSegments( array $set )
	{
		$this->segments = $set;
		return $this;
	}

	public function setBordered( $set = TRUE )
	{
		$this->bordered = $set;
		return $this;
	}

	public function add( $child, $width = 12, $mobile_width = 12, $offset = 0 )
	{
		parent::add( $child );
		$this->widths[] = array( $width, $mobile_width, $offset );
		return $this;
	}

	public function render()
	{
		$taken_width = 0;
		$taken_mobile_width = 0;
		$current_row = 0;
		$current_mobile_row = 0;

		$children = array();
		$key = 0;

// $this->segments = array( 8, 15, 22, 29 );

		foreach( $this->children as $child ){
			list( $width, $mobile_width, $offset ) = $this->widths[$key];
			$key++;

			$classes = array();
			if( $mobile_width == 12 ){
				if( $this->gutter ){
					$classes[] = 'hc-xs-mb' . $this->gutter;
				}
			}
			else {
				if( ! $this->bordered ){
					$classes[] = 'hc-xs-col';
				}

				if( strpos($mobile_width, '%') === FALSE ){
					$classes[] = 'hc-xs-col-' . $mobile_width;
				}
			}

			if( ! $this->bordered ){
				$classes[] = 'hc-col';
			}
			else {
				$classes[] = 'hc-table-cell';
				$classes[] = 'hc-border';
				if( $this->bordered !== TRUE ){
					$classes[] = 'hc-border-'. $this->bordered;
				}
			}

			if( strpos($width, '%') === FALSE ){
				$widthClass = 'hc-col-' . $width;
				$classes[] = $widthClass;
			}

			if( $this->gutter ){
				$classes[] = 'hc-px' . $this->gutter;
			}

			if( $this->gutter ){
				if( $current_row > 0 ){
					$classes[] = 'hc-mt' . $this->gutter;
				}
				if( $current_mobile_row > 0 ){
					$classes[] = 'hc-xs-mt' . $this->gutter;
				}
			}

			$slot = $this->htmlFactory->makeBlock( $child );
			foreach( $classes as $class ){
				$slot->addAttr('class', $class);
			}

		// more border, for weeks for example
			if( in_array($key, $this->segments) ){
				$slot
					->addAttr('class', 'hc-lg-prominent-border-left')
					// ->addAttr('style', 'border-left: gray 2px solid;')
					;
			}

			if( strpos($width, '%') !== FALSE ){
				$slot
					->addAttr('style', 'width: ' . $width . ';')
					// ->addAttr('style', 'border-left: gray 2px solid;')
					;
			}

			if( $offset ){
				$slot
					->addAttr('style', 'margin-left: ' . $offset . ';')
					;
			}

			$children[] = $slot;

			if( (strpos($width, '-') === FALSE) && (strpos($width, '%') === FALSE) ){
				$taken_width += $width;
				if( $taken_width >= 12 ){
					$current_row++;
					$taken_width = 0;

					$sep = $this->htmlFactory->makeBlock()
						->addAttr('class', 'hc-clearfix')
						;
					$children[] = $sep;
				}

				if( $mobile_width < 12 ){
					$taken_mobile_width += $mobile_width;
					if( $taken_mobile_width >= 12 ){
						$current_mobile_row++;
						$taken_mobile_width = 0;

						$sep = $this->htmlFactory->makeBlock()
							->addAttr('class', 'hc-xs-clearfix')
							;
						$children[] = $sep;
					}
				}
			}
		}

		$out = $this->htmlFactory->makeCollection( $children );
		$out = $this->htmlFactory->makeBlock($out)
			->addAttr('class', 'hc-clearfix')
			;

		if( $this->gutter ){
			$out->addAttr('class', 'hc-mxn' . $this->gutter);
		}

		if( $this->bordered ){
			$out
				->addAttr('class', 'hc-table-row')
				;
		}

		return $out;
	}
}