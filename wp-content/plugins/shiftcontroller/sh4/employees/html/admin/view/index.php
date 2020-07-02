<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Employees_Html_Admin_View_Index
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Request $request,

		SH4_Employees_Query $query,
		SH4_Calendars_Query $calendarsQuery,

		HC3_Users_Presenter $usersPresenter,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_App_Query $appQuery
		)
	{
		$this->request = $request;
		$this->ui = $ui;
		$this->layout = $layout;

		$this->usersPresenter = $hooks->wrap( $usersPresenter );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->query = $hooks->wrap($query);
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->appQuery = $hooks->wrap($appQuery);

		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$this->request
			->initParam('calendar', NULL)
			->initParam('status', 'active')
			;

		$params = $this->request->getParams();
		$currentStatus = $params['status'];

		switch( $currentStatus ){
			case 'all':
				$entries = $this->query->findAll();
				break;
			case 'active':
				$entries = $this->query->findActive();
				break;
			case 'archive':
				$entries = $this->query->findArchived();
				break;
		}

		$tableColumns = $this->self->listingColumns();

		$keys = array_keys( $tableColumns );
		$firstKey = array_shift( $keys );

		$tableRows = array();
		foreach( $entries as $e ){
			$row = $this->self->listingCell($e);

		// actions for first cell
			$itemMenu = $this->self->listingCellMenu( $e );
			$actions = $this->ui->helperActionsFromArray( $itemMenu );

			if( $actions ){
				$actions = $this->ui->makeListInline($actions)
					->gutter(1)
					->separated()
					;
				$row[$firstKey] = $this->ui->makeList( array($row[$firstKey], $actions) )->gutter(1);
			}

			$tableRows[] = $row;
		}

		$content = $this->ui->makeTable( $tableColumns, $tableRows );

		$byStatus = $this->self->byStatus( $entries );
		if( $byStatus ){
			$byStatusView = array();
			foreach( $byStatus as $bys ){
				list( $href, $hrefLabel, $countAddon ) = $bys;

				$thisSelected = FALSE;
				if( isset($href[1]['status']) && $href[1]['status'] == $currentStatus ){
					$thisSelected = TRUE;
				}

				if( $thisSelected ){
					$thisOne = $this->ui->makeSpan( $hrefLabel );
				}
				else {
					$thisOne = $this->ui->makeAhref( $href, $hrefLabel );
				}

				if( strlen($countAddon) ){
					$thisOne = $this->ui->makeListInline( array($thisOne, '(' . $countAddon . ')') )->gutter(1);
				}

				if( $thisSelected ){
					$thisOne
						->tag('font-style', 'bold')
						;
				}

				$byStatusView[] = $thisOne;
			}
			$byStatusView = $this->ui->makeListInline( $byStatusView )->separated();

			$content = $this->ui->makeList( array($byStatusView, $content) )->gutter(1);
		}

		$this->layout
			->setContent( $content )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}

	public function menu()
	{
		$return = array(
			'new' => array( 'admin/employees/new', '__Add New__'  )
			);

		if( defined('WPINC') ){
			$return['importwp'] = array( 'admin/employees/importwp',	'__Import WordPress Users__' );
		}

		$return['sort'] = array( 'admin/employees/sort',	'__Sort Order__' );

		return $return;
	}

	public function header()
	{
		$out = '__Employees__';
		return $out;
	}

	public function byStatus( $entries )
	{
		$return = array();

		$count1 = $this->query->countActive();
		$count2 = $this->query->countArchived();

		$return['all'] = array( array('admin/employees', array('status' => 'all')), '__All__', ($count1 + $count2) );

		if( $count1 = $this->query->countActive() ){
			$return['active'] = array( array('admin/employees', array('status' => 'active')), '__Active__', $count1 );
		}
		if( $count2 = $this->query->countArchived() ){
			$return['archived'] = array( array('admin/employees', array('status' => 'archive')), '__Archived__', $count2 );
		}

		return $return;
	}

	public function listingColumns()
	{
		$return = array(
			'title' 	=> '__Name__',
			'calendars' => '__Calendars__',
			'user' => '__Linked User Account__',
			);
		return $return;
	}

	public function listingCell( $model )
	{
		$return = array();
		$id = $model->getId();

		$titleView = $model->getTitle();

		if( $model->isArchived() ){
			$titleView = $this->ui->makeSpan($titleView)
				->tag('font-style', 'line-through')
				;
		}

		$titleView = $this->ui->makeSpan( $titleView )
			->tag('font-size', 4)
			->tag('font-style', 'bold')
			;

		$idView = $model->getId();
		$idLabel = $this->ui->makeBlock('id')
			->tag('mute')
			->tag('font-size', 2)
			;
		$idView = $this->ui->makeListInline( array($idLabel, $idView) )
			->gutter(1)
			;

		$descriptionView = $model->getDescription();
		$descriptionView = $this->ui->makeLongText( $descriptionView );

		$titleView = $this->ui->makeList( array($titleView, $descriptionView, $idView) )->gutter(0);

		$return['title'] = $titleView;

		$calendars = $this->appQuery->findCalendarsForEmployee( $model );

		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarsView[] = $this->calendarsPresenter->presentTitle($calendar);
		}
		$calendarsView = $this->ui->makeListInline( $calendarsView )->gutter(2);

		$calendarActions = array();
		$calendarActions[] = array( 'admin/employees/' . $id . '/calendars', '__Edit__' );

		if( $calendarActions ){
			$calendarActions = $this->ui->helperActionsFromArray( $calendarActions );
			$calendarActions = $this->ui->makeListInline( $calendarActions )->gutter(1)->separated();
			$calendarsView = $this->ui->makeList( array($calendarsView, $calendarActions) )->gutter(0);
		}

		$return['calendars'] = $calendarsView;

		$user = $this->appQuery->findUserByEmployee( $model );
		$userView = $user ? $this->usersPresenter->presentTitle( $user ) : '__N/A__';

		$userActions = array();
		if( $id > 0 ){
			if( $user ){
				$userActions[] = array( 'admin/employees/' . $id . '/user/0', NULL, '__Unlink__' );
			}
			else {
				$userActions[] = array( 'admin/employees/' . $id . '/user', '__Link To User Account__' );
			}
		}

		if( $userActions ){
			$userActions = $this->ui->helperActionsFromArray( $userActions );
			$userActions = $this->ui->makeListInline( $userActions )->gutter(1)->separated();
			$userView = $this->ui->makeList( array($userView, $userActions) )->gutter(0);
		}

		$return['user'] = $userView;

		return $return;
	}

	public function listingCellMenu( $model )
	{
		$id = $model->getId();

		$return = array();

		if( $id > 0 ){
			$return['edit'] = array( 'admin/employees/' . $id, '__Edit__' );

			if( $model->isArchived() ){
				$return['restore'] = array( 'admin/employees/' . $id . '/restore', NULL, '__Restore__');
				$return['delete'] = array( 'admin/employees/' . $id . '/delete', '__Delete__' );
			}
			else {
				$return['archive'] = array( 'admin/employees/' . $id . '/archive', NULL, '__Archive__' );
			}
		}

		return $return;
	}
}