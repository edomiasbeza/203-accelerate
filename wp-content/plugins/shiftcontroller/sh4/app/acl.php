<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_App_Acl
{
	public function __construct(
		HC3_Hooks $hooks,

		SH4_App_Query $appQuery,

		HC3_Auth $auth,
		HC3_IPermission $permission
		)
	{
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap( $permission );
	}

	public function checkUser()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$return = TRUE;
		return $return;
	}

	public function checkAdmin()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();

		if( ! $currentUserId ){
			return $return;
		}

		if( $this->permission->isAdmin($currentUser) ){
			$return = TRUE;
		}

		return $return;
	}

	public function checkManager()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$calendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( ! $calendars ){
			return $return;
		}

		$return = TRUE;

		return $return;
	}

	public function checkEmployee()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$employee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( ! $employee ){
			return $return;
		}

		$return = TRUE;

		return $return;
	}
}