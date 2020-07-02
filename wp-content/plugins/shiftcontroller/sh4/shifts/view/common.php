<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_View_ICommon
{
	public function breadcrumb( SH4_Shifts_Model $model );
	public function menu( SH4_Shifts_Model $model );
	public function icons( SH4_Shifts_Model $model, $iknow = array() );
}

class SH4_Shifts_View_Common implements SH4_Shifts_View_ICommon
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Session $session,
		HC3_Settings $settings,
		HC3_Ui $ui,
		SH4_Shifts_Presenter $presenter,
		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->ui = $ui;
		$this->presenter = $hooks->wrap($presenter);
		$this->session = $session;
		$this->conflicts = $hooks->wrap( $conflicts );
		$this->settings = $hooks->wrap( $settings );
	}

	public function breadcrumb( SH4_Shifts_Model $model )
	{
		$id = $model->getId();

		$return = array();

	// schedule link
		$scheduleLink = $this->session->getUserdata( 'scheduleLink' );
		if( ! $scheduleLink ){
			$scheduleLink = array( 'schedule', array() );
		}
		// $return['schedule'] = array( $scheduleLink, '__Schedule__' );

		$label = $this->presenter->presentTitle($model);
		$return['schedule/' . $id ] = array( 'shifts/' . $id , $label );
		// $return['schedule/' . $id ] = $label;

		return $return;
	}

	public function menu( SH4_Shifts_Model $model )
	{
		$id = $model->getId();

		$return = array();

		$conflicts = $this->conflicts->get( $model );
		if( $conflicts ){
			$label = '__View Conflicts__';
			$return['datetime']['conflicts'] = array( 'shifts/' . $id . '/conflicts', $label );
		}
		$return['datetime']['time'] = array( 'shifts/' . $id . '/time', '__Change Time__' );

		if( $model->isOpen() ){
			$return['employee']['assign'] = array( 
				array( 'shifts/--ID--/employee', $id ),
				'__Assign__'
				);
		}
		else {
			$return['employee']['change'] = array( 
				array( 'shifts/--ID--/employee', $id ),
				'__Change Employee__'
				);

			$return['employee']['unassign'] = array(
				'shifts/' . $id . '/employee/0',
				NULL,
				'__Unassign__'
				);
		}

		if( $model->isDraft() ){
			$return['status']['publish'] = array( 'shifts/' . $id . '/publish', NULL, '__Publish__' );
		}
		else {
			$noDraft = $this->settings->get('shifts_no_draft') ? TRUE : FALSE;
			if( ! $noDraft ){
				$return['status']['unpublish'] = array( 'shifts/' . $id . '/unpublish', NULL, '__Unpublish__' );
			}
		}
		$return['status']['delete'] = array( 'shifts/' . $id . '/delete', NULL, '__Delete__' );

		return $return;
	}

	public function icons( SH4_Shifts_Model $model, $iknow = array() )
	{
		$return = array();
		$calendar = $model->getCalendar();

		if( $model->isDraft() ){
			if( ! $calendar->isAvailability() ){
				$sign = $this->ui->makeBlock('?')
					->tag('align', 'center')
					->addAttr('style', 'width: 1em;')
					->tag('border')
					// ->tag('border-color', 'gray' )
					// ->tag('bgcolor', 'silver')
					->tag('bgcolor', 'gray')
					->tag('color', 'white')
					->tag('muted', 1)
					->addAttr('title', '__Draft__')
					;
				if( ! isset($return['status']) ){
					$return['status'] = array();
				}
				$return['status'][] = $sign;
			}
		}

		if( ! in_array('conflicts', $iknow) ){
			$conflicts = $this->conflicts->get( $model );

			if( $conflicts ){
				$sign = $this->ui->makeBlock('!')
					->tag('align', 'center')
					->addAttr('style', 'width: 1em;')
					->tag('border')
					->tag('border-color', 'red' )
					->tag('bgcolor', 'lightred')
					->tag('muted', 1)
					->addAttr('title', '__Conflicts__')
					;
				if( ! isset($return['datetime']) ){
					$return['datetime'] = array();
				}
				$return['datetime'][] = $sign;
			}
		}

		if( $model->isOpen() ){
			$sign = $this->ui->makeBlock('!')
				->tag('align', 'center')
				->addAttr('style', 'width: 1em;')
				->tag('border')
				// ->tag('border-color', 'orange' )
				// ->tag('bgcolor', 'yellow')
				->tag('bgcolor', 'orange')
				->tag('color', 'white')
				->tag('muted', 1)
				->addAttr('title', '__Open Shift__')
				;
			if( ! isset($return['employee']) ){
				$return['employee'] = array();
			}
			$return['employee'][] = $sign;
		}

		return $return;
	}
}