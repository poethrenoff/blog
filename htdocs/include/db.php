<?php
abstract class db
{
	private static $db_driver = null;
	
	private static function get_driver()
	{
		if ( self::$db_driver == null )
			self::$db_driver = db_driver::factory();
		
		return self::$db_driver;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////
	
	public static function query( $query, $fields = array() )
	{
		return self::get_driver() -> query( $query, $fields );
	}
	
	public static function select_cell( $query, $fields = array() )
	{
		return self::get_driver() -> select_cell( $query, $fields );
	}
	
	public static function select_row( $query, $fields = array() )
	{
		return self::get_driver() -> select_row( $query, $fields );
	}
	
	public static function select_all( $query, $fields = array() )
	{
		return self::get_driver() -> select_all( $query, $fields );
	}
	
	public static function insert( $table, $fields = array() )
	{
		return self::get_driver() -> insert( $table, $fields );
	}
	
	public static function update( $table, $fields = array(), $where = array() )
	{
		return self::get_driver() -> update( $table, $fields, $where );
	}
	
	public static function delete( $table, $where = array() )
	{
		return self::get_driver() -> delete( $table, $where );
	}
	
	public static function last_insert_id()
	{
		return self::get_driver() -> last_insert_id();
	}
	
	public static function create()
	{
		return self::get_driver() -> create();
	}
}
