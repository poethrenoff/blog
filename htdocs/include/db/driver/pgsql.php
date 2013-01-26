<?php
class db_driver_pgsql extends db_driver
{
	protected function __construct( $db_type, $db_host, $db_port, $db_name, $db_user, $db_password )
	{
		try {
			$this -> dbh = new PDO( "{$db_type}:host={$db_host};dbname={$db_name}" . ( $db_port ? ";port={$db_port}" : "" ),
				$db_user, $db_password, array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ) );
		}
		catch ( Exception $e ) {
			throw new Exception( $e -> getMessage(), true );
		}
	}
	
	public function last_insert_id()
	{
		return $this -> select_cell( 'select lastval()' );
	}
	
	public function create()
	{
		$sql = "<pre>\n";
		
		foreach ( metadata::$objects as $object_name => $object_desc )
		{
			if ( !( isset( $object_desc['fields'] ) && $object_desc['fields'] ) )
				continue;
			
			$sql .= "drop table if exists {$object_name};\n";
			$sql .= "create table {$object_name} (\n";
			
			$fields = array(); $pk_field = '';
			foreach ( $object_desc['fields'] as $field_name => $field_desc )
			{
				switch ( $field_desc['type'] )
				{
					case 'pk': $type = "serial"; $pk_field = $field_name; break;
					case 'string': case 'select': case 'image': case 'file': case 'password';
						$type = "varchar"; break;
					case 'date': case 'datetime': $type = "varchar(14)"; break;
					case 'text': $type = "text"; break;
					case 'int': $type = "integer"; break;
					case 'float': $type = "numeric"; break;
					case 'active': case 'boolean': case 'order':
					case 'default': case 'table': case 'parent':
						$type = "integer"; break;
					default: $type = "error";
				}
				$fields[] = "\t{$field_name} {$type}";
			}
			if ( $pk_field )
				$fields[] = "\tprimary key ({$pk_field})";
			
			$sql .= join( ",\n", $fields ) . "\n";
			$sql .= ");\n\n";
		}
		
		$sql .= "</pre>\n";
		
		print $sql;
		
		exit;
	}
}
