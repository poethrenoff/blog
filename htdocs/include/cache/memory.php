<?php
class cache_memory
{
	private static $cache_obj = null;
	
	private function get_connect()
	{
		if ( !is_null( self::$cache_obj ) )
			return self::$cache_obj;
		
		if ( !class_exists( 'Memcache', false ) )
			return self::$cache_obj = false;
		
		$cache_obj = new Memcache();
		if ( @$cache_obj -> pconnect( CACHE_HOST, CACHE_PORT ) )
			return self::$cache_obj = $cache_obj;
		
		return self::$cache_obj = false;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function get( $key, $expire )
	{
		if ( $cache_obj = self::get_connect() )
			return $cache_obj -> get( $key );
		
		return false;
	}
	
	public function set( $key, $var, $expire )
	{
		if ( $cache_obj = self::get_connect() )
			return $cache_obj -> set( $key, $var, false, $expire );
		
		return false;
	}
	
	public function clear()
	{
		if ( $cache_obj = self::get_connect() )
			return $cache_obj -> flush();
		
		return false;
	}
}