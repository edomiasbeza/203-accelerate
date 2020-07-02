<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Announce
{
	public function __construct( 
		HC3_Ui $ui,
		HC3_Session $session
		)
	{
		$this->ui = $ui;
		$this->session = $session;
	}

	public function render( $out )
	{
		$message = $this->session->getFlashdata('message');
		$error = $this->session->getFlashdata('error');
		$debug = $this->session->getFlashdata('debug');

		// $message = 'test';

		if( ! ( $message OR strlen($message) OR strlen($debug) OR $error OR strlen($error)) ){
			return $out;
		}

		if( strlen($debug) ){
			$debug = $this->ui->makeBlock( $debug )
				->tag('padding', 2)
				->tag('margin', 'y2')
				->tag('border')
				// ->tag('auto-dismiss')
				->tag('rounded')
				->tag('border-color', 'orange')
				;
			$out = $this->ui->makeList( array($debug, $out) );
		}

		if( $message OR strlen($message) OR strlen($debug) OR $error OR strlen($error) ){
			if( is_array($message) ){
				$message = $this->ui->makeList($message)->gutter(0);
			}
			if( $message ){
				$message = $this->ui->makeBlock( $message )
					->tag('padding', 2)
					->tag('margin', 'y2')
					->tag('auto-dismiss')
					->tag('rounded')

					->tag('bgcolor', 'lightgreen')
					->tag('muted', 1)

					->tag('border', 'olive')
					->tag('border-color', 'olive')
					->tag('color', 'black')
					;
			}

			if( is_array($error) ){
				$error = $this->ui->makeList($error)->gutter(0);
			}
			if( $error ){
				$error = $this->ui->makeBlock( $error )
					->tag('padding', 2)
					->tag('margin', 'y2')
					->tag('auto-dismiss')
					->tag('rounded')

					->tag('bgcolor', 'lightred')
					->tag('muted', 1)

					->tag('border', 'maroon')
					->tag('border-color', 'maroon')
					->tag('color', 'black')
					;

				if( $message ){
					$message = $this->ui->makeCollection( array($error, $message) );
					$message = $this->ui->makeBlock( $message );
				}
				else {
					$message = $error;
				}
			}

			$message
				->addAttr('style', 'position: absolute; left: .5em; top: .5em; right: .5em; z-index: 1000;')
				;

			$out = $this->ui->makeList( array($message, $out) );
		}

		return $out;
	}
}