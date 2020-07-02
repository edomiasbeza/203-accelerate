<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Settings implements HC3_ISettings
{
	protected $prefix = NULL;

	protected $defaults = array();
	protected $loaded = array();

	public function __construct( $prefix )
	{
		$this->prefix = $prefix;
	}

	public function init( $name, $value )
	{
		$this->defaults[$name] = $value;
		return $this;
	}

	public function set( $name, $value )
	{
		update_option( $this->prefix . $name, $value );

		$this->loaded[ $name ] = $value;
		return $this;
	}

	public function resetAll()
	{
		$allOptions = wp_load_alloptions();
		foreach( $allOptions as $option => $value ){
			if( strpos($option, $this->prefix) === 0 ){
				delete_option( $option );
			}
		}
	}

	public function reset( $name )
	{
		delete_option( $this->prefix . $name );
		unset( $this->loaded[$name] );
		return $this;
	}

	public function get( $name, $wantArray = FALSE )
	{
		$return = NULL;

		if( array_key_exists($name, $this->loaded) ){
			$return = $this->loaded[$name];
		}
		else {
			$default = array_key_exists($name, $this->defaults) ? $this->defaults[$name] : NULL;
			$return = get_option( $this->prefix . $name, $default );
			if( NULL !== $return ){
				$this->loaded[$name] = $return;
			}
		}

		if( $wantArray && (! is_array($return)) ){
			$return = ( NULL === $return ) ? array() : array($return);
		}

		if( (! $wantArray) && is_array($return) ){
			$return = array_shift( $return );
		}

		return $return;
	}
}