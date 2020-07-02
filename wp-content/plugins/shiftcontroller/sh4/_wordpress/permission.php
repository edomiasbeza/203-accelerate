<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Permission implements HC3_IPermission
{
	protected $_adminRoles = array( 'administrator', 'developer', 'sh4_admin' );

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Users_Query $usersQuery
		)
	{
		$this->usersQuery = $hooks->wrap( $usersQuery );
	}

	public function isAdmin( HC3_Users_Model $user )
	{
		$return = FALSE;

		$id = $user->getId();
		$wpUser = get_userdata( $id );

		if( ! isset($wpUser->roles) ){
			return $return;
		}

		$thisRoles = $wpUser->roles;
		if( array_intersect($this->_adminRoles, $thisRoles) ){
			$return = TRUE;
		}

		return $return;
	}

	public function findAdmins()
	{
		$args = array();
		$args[] = array('role', 'IN', $this->_adminRoles);

		$return = $this->usersQuery->read( $args );
		return $return;
	}
}