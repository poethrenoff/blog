<?php
class valid
{
	public static function int( $content )
	{
		return preg_match( '/^\-?\+?\d+$/', $content );
	}
	
	public static function float( $content )
	{
		return preg_match( '/^\-?\+?\d+[\.,]?\d*$/', $content );
	}
	
	public static function date( $content )
	{
		return preg_match( '/^(\d{2})\.(\d{2})\.(\d{4})$/', $content, $match ) && 
			checkdate ( $match[2], $match[1], $match[3] );
	}
	
	public static function datetime( $content )
	{
		return preg_match( '/^(\d{2})\.(\d{2})\.(\d{4}) (\d{2})\:(\d{2})$/', $content, $match ) && 
			checkdate ( $match[2], $match[1], $match[3] ) &&
				( $match[4] >= 0 && $match[4] <= 23 && $match[5] >= 0 && $match[5] <= 59 );
	}
	
	public static function email( $content )
	{
		return preg_match( '/^[a-z0-9_\.-]+@[a-z0-9_\.-]+\.[a-z]{2,}$/i', $content );
	}
	
	public static function alpha( $content )
	{
		return preg_match( '/^[a-z0-9_]+$/i', $content );
	}
}