<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Calendars_Html_Admin_View_Managers
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_App_Query $appQuery,
		HC3_IPermission $permission,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		HC3_Users_Presenter $usersPresenter,
		HC3_Users_Query $users
	)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->permission = $hooks->wrap($permission);
		$this->appQuery = $hooks->wrap($appQuery);
		$this->calendarsQuery = $hooks->wrap($calendarsQuery);
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->usersPresenter = $hooks->wrap($usersPresenter);
		$this->users = $hooks->wrap($users);
	}

	public function render( $id )
	{
		$model = $this->calendarsQuery->findById($id);

		$currentManagers = $this->appQuery->findManagersForCalendar( $model );
		$currentManagersIds = array_keys( $currentManagers );

		$currentViewers = $this->appQuery->findViewersForCalendar( $model );
		$currentViewersIds = array_keys( $currentViewers );

		$users = $this->users->findAll();
		$users = $currentViewers + $users;
		$users = $currentManagers + $users;

		$header = array(
			'title'		=> '__User__',
			'manager'	=> '__Is Manager__',
			'viewer'		=> '__Is Viewer__',
			);

		$keys = array_keys( $header );
		$firstKey = array_shift( $keys );

		$rows = array();
		foreach( $users as $e ){
			$userId = $e->getId();
			$row = array();

			$userView = $this->usersPresenter->presentTitle( $e );
			$row['title'] = $userView;

			$isManagerView = NULL;
			if( in_array($userId, $currentManagersIds) ){
				$isManagerView = '__Yes__';
				$isManagerView = $this->ui->makeSpan($isManagerView)
					->tag('padding', 'x1')
					->tag('color', 'white')
					->tag('bgcolor', 'olive')
					;
			}
			else {
				$isManagerView = '__No__';
				$isManagerView = $this->ui->makeSpan($isManagerView)
					->tag('padding', 'x1')
					->tag('color', 'white')
					->tag('bgcolor', 'gray')
					;
			}
			$row['manager'] = $isManagerView;

			$isViewerView = NULL;
			if( in_array($userId, $currentManagersIds) ){
				$isManagerView = '__Yes__';
				$isManagerView = $this->ui->makeSpan($isManagerView)
					->tag('padding', 'x1')
					->tag('color', 'white')
					->tag('bgcolor', 'olive')
					;
			}
			else {

				if( in_array($userId, $currentViewersIds) ){
					$isViewerView = '__Yes__';
					$isViewerView = $this->ui->makeSpan($isViewerView)
						->tag('padding', 'x1')
						->tag('color', 'white')
						->tag('bgcolor', 'olive')
						;
				}
				else {
					$isViewerView = '__No__';
					$isViewerView = $this->ui->makeSpan($isManagerView)
						->tag('padding', 'x1')
						->tag('color', 'white')
						->tag('bgcolor', 'gray')
						;
				}
			}
			$row['viewer'] = $isViewerView;

			$actions = array();
			if( in_array($userId, $currentManagersIds) ){
				if( ! $this->permission->isAdmin($e) ){
					$actions[] = array( 'admin/calendars/' . $id . '/managers/' . $userId . '/remove', NULL, '__Remove From Managers__' );
				}
			}
			else {
				$actions[] = array( 'admin/calendars/' . $id . '/managers/' . $userId . '/add', NULL, '__Add To Managers__' );

				if( in_array($userId, $currentViewersIds) ){
					if( ! $this->permission->isAdmin($e) ){
						$actions[] = array( 'admin/calendars/' . $id . '/viewers/' . $userId . '/remove', NULL, '__Remove From Viewers__' );
					}
				}
				else {
					$actions[] = array( 'admin/calendars/' . $id . '/viewers/' . $userId . '/add', NULL, '__Add To Viewers__' );
				}
			}

			if( $actions ){
				$actions = $this->ui->helperActionsFromArray( $actions );
				if( $actions ){
					$actions = $this->ui->makeListInline( $actions )->separated()->gutter(1);
					$row[$firstKey] = $this->ui->makeList( array($row[$firstKey], $actions) )->gutter(1);
				}
			}

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header( $model )
	{
		$out = '__Managers__';
		return $out;
	}

	public function breadcrumb( $model )
	{
		$calendarId = $model->getId();
		$calendarTitle = $this->calendarsPresenter->presentTitle( $model );

		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$return['calendars/edit'] = $calendarTitle;
		return $return;
	}
}
