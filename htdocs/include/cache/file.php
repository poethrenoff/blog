<?php
class cache_file
{
	private $cache_ext = '.cache';
	
	private function get_file_name( $key )
	{
		return CACHE_DIR . $key . $this -> cache_ext;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function get( $key, $expire )
	{
		$file_name = $this -> get_file_name( $key );
		
		if ( !file_exists( $file_name ) )
			return false;
		
		if ( filemtime( $file_name ) + $expire < time() )
			return false;
		
		$var = @file_get_contents( $file_name );
		
		return @unserialize( $var );
	}
	
	public function set( $key, $var, $expire )
	{
		$file_name = $this -> get_file_name( $key );
		
		@file_put_contents( $file_name, serialize( $var ) );
		
		return file_exists( $file_name );
	}
	
	public function clear()
	{
		$file_list = array_diff( scandir( CACHE_DIR ), array( '.', '..' ) );
		
		foreach( $file_list as $file_name )
		{
			$file_path = CACHE_DIR . '/' . $file_name;
			if ( is_file( $file_path ) && preg_match( '/' . preg_quote( $this -> cache_ext ) . '$/', $file_name ) )
				@unlink( $file_path );
		}
		
		return true;
	}
}