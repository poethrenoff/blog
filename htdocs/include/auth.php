<?php
class auth
{
	private static $admin = null;
	
	private static $object_list = array();
	
	public static function login()
	{
		if ( session::flash( 'logout') )
			self::unauthorized();
		
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) )
			self::$admin = db::select_row( '
					select * from admin where admin_login = :admin_login and admin_password = :admin_password and admin_active = 1',
				array( 'admin_login' => $_SERVER['PHP_AUTH_USER'], 'admin_password' => md5( $_SERVER['PHP_AUTH_PW'] ) ) );
		
		if ( !self::$admin )
			self::unauthorized();
		
		$role_list = db::select_all( '
				select role.role_id, role.role_default
				from role, admin_role
				where admin_role.role_id = role.role_id and
					admin_role.admin_id = :admin_id
				order by role.role_default desc',
			array( 'admin_id' => self::$admin['admin_id'] ) );
		
		if ( count( $role_list ) )
		{
			if ( $role_list[0]['role_default'] )
				$object_list = db::select_all( '
					select object_id, object_name from object' );
			else
				$object_list = db::select_all( '
					select object.object_id, object_name from object, role_object
					where role_object.object_id = object.object_id and
						role_id in ( ' . array_make_in( $role_list, 'role_id' ) . ' )' );
			
			foreach ( $object_list as $object_item )
				self::$object_list[$object_item['object_id']] = $object_item['object_name'];
		}
	}
	
	public static function get_object_tree( $current_object )
	{
		$object_list = db::select_all( '
			select * from object where object_active = 1 and
				object_id in ( ' . array_make_in( array_keys( self::$object_list ) ) . ' )
			order by object_order' );
		$object_tree = tree::get_tree( $object_list, 'object_id', 'object_parent' );
		
		foreach ( $object_tree as $object_index => $object_item )
		{
			if ( $object_item['object_name'] == $current_object )
				$object_tree[$object_index]['_selected'] = true;
			if ( !is_empty( $object_item['object_name'] ) )
				$object_tree[$object_index]['object_url'] = 
					url_for( array( 'controller' => controller(), 'object' => $object_item['object_name'] ) );
		}
		
		return $object_tree;
	}
	
	public static function logout()
	{
		session::flash( 'logout', true );
		
		header( 'Location: /admin/' );
		exit;
	}
	
	public static function unauthorized()
	{
		header( 'WWW-Authenticate: Basic realm="Administration interface"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		
		throw new Exception( 'Извините, у вас нет прав доступа к этой странице.' );
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////
	
	public static function get_admin()
	{
		return self::$admin;
	}
	
	public static function get_object_list()
	{
		return self::$object_list;
	}
}
