<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Schedule_Html_View_IControlOptions
{
	public function render();
	public function renderMore();
	public function renderFilterEmployees();
	public function renderFilterCalendars();
	public function renderFilter();
	public function renderGroupBy();
	public function filterViewTypes( $return );
	public function renderType();
}

class SH4_Schedule_Html_View_ControlOptions implements SH4_Schedule_Html_View_IControlOptions
{
	public function __construct( 
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_UriAction $uriAction,
		HC3_Time $t,

		SH4_Employees_Query $employeesQuery,
		SH4_Schedule_Html_View_Common $viewCommon
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->request = $request;
		$this->uriAction = $uriAction;
		$this->t = $t;

		$this->viewCommon = $hooks->wrap( $viewCommon );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
	}

	public function render()
	{
	// view options
		$out = array();

		$type = $this->self->renderType();
		if( NULL !== $type ){
			$out[] = $type;
		}

		$groupBy = $this->self->renderGroupBy();
		if( NULL !== $groupBy ){
			$out[] = $groupBy;
		}

		$filter = $this->self->renderFilter();
		if( NULL !== $filter ){
			$out[] = $filter;
		}

		$more = $this->self->renderMore();
		if( NULL !== $more ){
			$out[] = $more;
		}

		$out = $this->ui->makeGrid( $out );

		return $out;
	}

	public function renderMore()
	{
		$out = NULL;

		$slug = $this->request->getSlug();
		$params = $this->request->getParams();
		$toParams = $this->request->getParams('withoutdefault');

		$hideui = $params['hideui'];

		$options = array();

		if( ! in_array('download', $hideui) ){
			$thisParams = $toParams;
			$thisParams['download'] = 1;

			// $options['download'] = $this->ui->makeAhref( array($slug, $thisParams), '__Download__' )
			// 	->tag('tab-link')
			// 	;

			$to = $this->uriAction->makeUrl( array($slug, $thisParams) );
			$options['download'] = $this->ui->makeAhref( $to, '__Download__' )
				->tag('tab-link')
				;
		}

		if( ! in_array('print', $hideui) ){
			$thisParams = $toParams;
			$thisParams['print'] = 1;
			$options['print'] = $this->ui->makeAhref( array($slug, $thisParams), '__Print__' )
				->tag('tab-link')
				->newWindow()
				->actionMode()
				;
		}

		if( ! $options ){
			return $out;
		}

		if( count($options) == 1 ){
			$out = array_shift( $options );
			return $out;
		}

		$out = $this->ui->makeList( $options )
			->gutter(1)
			;

		$label = '__More__';
		$label = $this->ui->makeSpan($label)
			->tag('font-size', 2)
			->tag('muted')
			;

		$out = $this->ui->makeCollapse( $label, $out )
			->border(FALSE)
			;
		return $out;
	}

	public function renderFilterEmployees()
	{
		$return = NULL;

		$slug = $this->request->getSlug();
		$params = $this->request->getParams();
		$toParams = $this->request->getParams('withoutdefault');

		$hideui = $params['hideui'];
		if( in_array('filter-employee', $hideui) ){
			return $return;
		}

		if( ! array_key_exists('employee', $toParams) ){
			$toParams['employee'] = array();
		}

		$all = $this->viewCommon->findAllEmployees();

		$assignedShiftEmployee = $this->employeesQuery->findById( -1 );
		if( isset($all[0]) ){
			$openShiftEmployee = $all[0];
			$all[ $assignedShiftEmployee->getId() ] = $assignedShiftEmployee;
			unset( $all[0] );
			$all = array( 
				$openShiftEmployee->getId() => $openShiftEmployee,
				$assignedShiftEmployee->getId() => $assignedShiftEmployee
				) + $all;
		}

		if( count($all) < 2 && (! $toParams['employee']) ){
			return $return;
		}

		$allIds = array_keys($all);
		$selectedEmployees = array();
		foreach( $allIds as $id ){
			if( in_array($id, $toParams['employee']) ){
				$selectedEmployees[$id] = $all[$id];
				unset($all[$id]);
			}
		}

		if( $selectedEmployees ){
			$selectedVview = array();
			foreach( $selectedEmployees as $e ){
				$thisParams = $toParams;

				$e_view = $e->getTitle();

				$thisParams['employee'] = HC3_Functions::removeFromArray( $thisParams['employee'], $e->getId() );
				if( ! $thisParams['employee'] ){
					$thisParams['employee'] = array('x');
				}
				$link = $this->ui->makeAhref( array($slug, $thisParams), '&times' );

				$e_view = $this->ui->makeListInline( array($link, $e_view) )->gutter(1);
				$e_view = $this->ui->makeBlock( $e_view )
					->padding(1)
					->tag('border')
					;

				$selectedView[] = $e_view;
			}
			$selectedView = $this->ui->makeList( $selectedView )->gutter(1);
		}

		$toSelectView = array();
		foreach( $all as $e ){
			$thisParams = $toParams;

			$thisParams['employee'][] = $e->getId();
			$label = $e->getTitle();
			$eView = $this->ui->makeAhref( array($slug, $thisParams), $label )
				->tag('tab-link')
				;
			$toSelectView[] = $eView;

			// if( 0 == $e->getId() ){
				// $thisParams = $toParams;
				// $thisParams['employee'][] = $assignedShiftEmployee->getId();

				// $label = $assignedShiftEmployee->getTitle();
				// $eView = $this->ui->makeAhref( array($slug, $thisParams), $label )
					// ->tag('tab-link')
					// ;
				// $toSelectView[] = $eView;
			// }
		}

		if( ! ($selectedEmployees OR $toSelectView) ){
			return $return;
		}

		$view = array();

		if( $selectedEmployees ){
			$view[] = $selectedView;
		}

		if( $toSelectView ){
			$toSelectView = $this->ui->makeList( $toSelectView )
				->gutter(0)
				;

			if( $selectedEmployees ){
				$label = '&#43; ' . '__More Employees__';
				$label = $this->ui->makeBlock($label)
					->tag('font-size', 2)
					;
			}
			else {
				$label = '__All Employees__';
			}

			$toSelectView = $this->ui->makeCollapse( $label, $toSelectView );
			$view[] = $toSelectView;
		}

		$view = $this->ui->makeList( $view )->gutter(1);
		$return = $this->ui->makeLabelled( '__Employee__', $view );

		return $return;
	}

	public function renderFilterCalendars()
	{
		$return = NULL;

		$slug = $this->request->getSlug();
		$params = $this->request->getParams();
		$toParams = $this->request->getParams('withoutdefault');

		$hideui = $params['hideui'];
		if( in_array('filter-calendar', $hideui) ){
			return $return;
		}

		if( ! array_key_exists('calendar', $toParams) ){
			$toParams['calendar'] = array();
		}

		$all = $this->viewCommon->findAllCalendars();

		if( count($all) < 2 && (! $toParams['calendar']) ){
			return $return;
		}

		$allIds = array_keys($all);
		$selectedCalendars = array();

		foreach( $allIds as $id ){
			if( in_array($id, $toParams['calendar']) ){
				$selectedCalendars[$id] = $all[$id];
				unset($all[$id]);
			}
		}

		if( $selectedCalendars ){
			$selectedVview = array();
			foreach( $selectedCalendars as $e ){
				$thisParams = $toParams;

				$eView = $e->getTitle();

				$thisParams['calendar'] = HC3_Functions::removeFromArray( $thisParams['calendar'], $e->getId() );
				if( ! $thisParams['calendar'] ){
					$thisParams['calendar'] = array('x');
				}
				$link = $this->ui->makeAhref( array($slug, $thisParams), '&times' );

				$color = $this->ui->makeBlock('&nbsp;')->tag('bgcolor', $e->getColor())->paddingX(1);
				$eView = $this->ui->makeListInline( array($link, $color, $eView) )->gutter(1);
				$eView = $this->ui->makeBlock( $eView )
					->padding(1)
					->tag('border')
					;

				$selectedView[] = $eView;
			}
			$selectedView = $this->ui->makeList( $selectedView )->gutter(1);
		}

		$toSelectView = array();
		foreach( $all as $e ){
			$thisParams = $toParams;
			$thisParams['calendar'][] = $e->getId();

			$label = $e->getTitle();
			$color = $this->ui->makeBlockInline('&nbsp;')->paddingX(1)->tag('bgcolor', $e->getColor() );
			$label = $this->ui->makeListInline( array($color, $label) )->gutter(1);

			$eView = $this->ui->makeAhref( array($slug, $thisParams), $label )
				->tag('tab-link')
				;

			$toSelectView[] = $eView;
		}

		if( ! ($selectedCalendars OR $toSelectView) ){
			return $return;
		}

		$view = array();

		if( $selectedCalendars ){
			$view[] = $selectedView;
		}

		if( $toSelectView ){
			$toSelectView = $this->ui->makeList( $toSelectView )
				->gutter(0)
				;

			if( $selectedCalendars ){
				$label = '&#43; ' . '__More Calendars__';
				$label = $this->ui->makeBlock($label)
					->tag('font-size', 2)
					;
			}
			else {
				$label = '__All Calendars__';
			}

			$toSelectView = $this->ui->makeCollapse( $label, $toSelectView );
			$view[] = $toSelectView;
		}

		$view = $this->ui->makeList( $view )->gutter(1);
		$return = $this->ui->makeLabelled( '__Calendar__', $view );

		return $return;
	}

	public function renderFilter()
	{
		$return = NULL;

		$params = $this->request->getParams('withoutdefault');

		if( ! array_key_exists('employee', $params) ){
			$params['employee'] = array();
		}
		if( ! array_key_exists('calendar', $params) ){
			$params['calendar'] = array();
		}

		$filterView = array();

	// filter - employees
		$filterViewEmployees = $this->self->renderFilterEmployees();
		if( $filterViewEmployees ){
			$filterView[] = $filterViewEmployees;
		}

	// calendars
		$filterViewCalendars = $this->self->renderFilterCalendars();
		if( $filterViewCalendars ){
			$filterView[] = $filterViewCalendars;
		}

		if( ! $filterView ){
			return $return;
		}

		$filter = $this->ui->makeList( $filterView );

		$label = '__Filter__';
		$currentView = '';
		$totalSelectedCount = count($params['calendar']) + count($params['employee']);
		$currentView = $totalSelectedCount ? '(' . $totalSelectedCount . ')' : '__None__';

		$label = '__Filter__';
		$label = $this->ui->makeSpan($label)
			->tag('font-size', 2)
			->tag('muted')
			;
		$currentView = $this->ui->makeSpan($currentView)
			->tag('font-style', 'bold')
			;
		$label = $this->ui->makeListInline( array($label, $currentView) )->gutter(1);

		$filter = $this->ui->makeCollapse( $label, $filter )
			->border(FALSE)
			;

		return $filter;
	}

	public function renderGroupBy()
	{
		$return = NULL;

		$slug = $this->request->getSlug();
		$params = $this->request->getParams();

		$hideui = $params['hideui'];
		if( in_array('groupby', $hideui) ){
			return $return;
		}

		$current = $params['groupby'];
		$currentView = $current;

		$employees = $this->viewCommon->getEmployees();
		$calendars = $this->viewCommon->getCalendars();

		$options = array();

		if( (count($employees) > 1) OR ('employee' == $current) ){
			$options[] = array( '__Employee__', 'employee' );
		}
		if( (count($calendars) > 1) OR ('calendar' == $current) ){
			$options[] = array( '__Calendar__', 'calendar' );
		}

		if( ! $options ){
			return $return;
		}

		array_unshift( $options, array( '__None__', 'none' ) );

		$view = array();
		foreach( $options as $option ){
			list( $label, $k ) = $option;

			if( $k == $current ){
				$currentView = $label;
				continue;
			}

			$params = $k ? array('groupby' => $k) : array('groupby' => NULL);
			$this_option = $this->ui->makeAhref( array($slug, $params), $label )
				->tag('tab-link')
				;
			$view[] = $this_option;
		}

		$out = $this->ui->makeList( $view )
			->gutter(1)
			;

		$label = '__Group By__';
		$label = $this->ui->makeSpan($label)
			->tag('font-size', 2)
			->tag('muted')
			;
		$currentView = $this->ui->makeSpan($currentView)
			->tag('font-style', 'bold')
			;
		$label = $this->ui->makeListInline( array($label, $currentView) )->gutter(1);

		$out = $this->ui->makeCollapse( $label, $out )
			->border(FALSE)
			;
		return $out;
	}

	public function filterViewTypes( $return )
	{
		return $return;
	}

	public function renderType()
	{
		$out = NULL;

		$slug = $this->request->getSlug();
		$params = $this->request->getParams();
		$toParams = $this->request->getParams('withoutdefault');

		$hideui = $params['hideui'];
		if( in_array('type', $hideui) ){
			return $out;
		}

		$current = $params['type'];
		$currentView = $current;

		$options = array(
			'day'		=> '__Day__',
			'week'	=> '__Week__',
			'month'	=> '__Month__',
			'4weeks'	=> '__4 Weeks__',
			'list'	=> '__List__',
			'report'	=> '__Report__'
			);

		$keys = array_keys( $options );
		foreach( $keys as $key ){
			$testKey = 'type-' . $key;
			if( in_array($testKey, $hideui) ){
				unset( $options[$key] );
			}
		}

		if( count($options) < 2 ){
			return $out;
		}

		$options = $this->viewCommon->filterViewTypes( $options );

		$view = array();
		foreach( $options as $k => $label ){
			$thisParams = $toParams;
			$thisParams['type'] = $k;
			if( in_array($k, array('week', 'month', 'day', '4weeks')) ){
				$thisParams['end'] = NULL;
				$thisParams['time'] = NULL;
			}

			if( 'day' == $k ){
			// if in current week/month then jump to today rather than the first day of week/month
				$today = $this->t->setNow()->setStartDay()->formatDateDb();
				if( isset($params['start']) && isset($params['end']) ){
					if( ($today >= $params['start']) && ($today <= $params['end']) ){
						$thisParams['start'] = $today;
					}
				} 
			}

			if( $current == $k ){
				$currentView = $label;
				continue;
			}

			$thisOption = $this->ui->makeAhref( array($slug, $thisParams), $label )
				->tag('tab-link')
				;
			$view[] = $thisOption;
		}

		$out = $this->ui->makeList( $view )
			->gutter(1)
			;

		$label = '__View__';
		$label = $this->ui->makeSpan($label)
			->tag('font-size', 2)
			->tag('muted')
			;
		$currentView = $this->ui->makeSpan($currentView)
			->tag('font-style', 'bold')
			;
		$label = $this->ui->makeListInline( array($label, $currentView) )->gutter(1);

		$out = $this->ui->makeCollapse( $label, $out )
			->border(FALSE)
			;
		return $out;
	}
}