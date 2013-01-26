<?php
class tree
{
	private static $primary_field = '';
	
	private static $parent_field = '';
	
	private static $records_by_parent = array();
	
	private static $records_as_tree = array();
	
	private static $except = array();
	
	public static function get_tree( &$records, $primary_field, $parent_field, $begin = 0, $except = array() )
	{
		self::$primary_field = $primary_field;
		self::$parent_field = $parent_field;
		self::$except = $except;
		
		self::$records_by_parent = array();
		foreach ( $records as $record )
			if ( isset( $record[self::$parent_field] ) )
				self::$records_by_parent[$record[self::$parent_field]][] = $record;
		
		self::$records_as_tree = array();
		self::build_tree( $begin );
		
		return self::$records_as_tree;
	}
	
	private static function build_tree( $parent_field_id, $depth = 0 )
	{
		if ( isset( self::$records_by_parent[$parent_field_id] ) )
		{
			foreach ( self::$records_by_parent[$parent_field_id] as $record )
			{
				if ( isset( $record[self::$primary_field] ) &&
					!in_array( $record[self::$primary_field], self::$except ) )
				{
					$record['_depth'] = $depth;
					$record['_has_children'] = isset( self::$records_by_parent[$record[self::$primary_field]] );
					if ( $record['_has_children'] )
						$record['_children_count'] = count( self::$records_by_parent[$record[self::$primary_field]] );
					
					self::$records_as_tree[] = $record;
					
					self::build_tree( $record[self::$primary_field], $depth + 1 );
				}
			}
		}
	}
}
