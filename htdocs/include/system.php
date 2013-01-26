<?php
class system
{
	private static $routes = array();
	
	private static $route_params = array();
	
	private static $cache_mode = true;
	
	private static $lang = null;
	
	private static $lang_list = array();
	
	private static $site = null;
	
	private static $page = null;
	
	public static function init()
	{
		self::$site = cache::get( CONFIG_FILE );
		if ( self::$site === false )
			self::$site = build();
		
		if ( !self::$site ) exit;
		
		if ( isset( self::$site['lang'] ) )
		{
			foreach ( self::$site['lang'] as $lang )
			{
				self::$lang_list[$lang['lang_name']] = $lang['lang_id'];
				
				if ( $lang['lang_default'] )
				{
					self::$lang = $lang['lang_name'];
					
					if ( isset( $lang['dictionary'] ) )
						foreach ( $lang['dictionary'] as $dictionary )
							if ( !defined( $dictionary['word_name'] ) )
								define( $dictionary['word_name'], $dictionary['word_value'], true );
				}
			}
		}
		
		if ( isset( self::$site['preference'] ) )
		{
			foreach ( self::$site['preference'] as $preference )
				if ( !defined( $preference['preference_name'] ) )
					define( $preference['preference_name'], $preference['preference_value'], true );
		}
	}
	
	public static function dispatcher()
	{
		session_start();
		session::start();
		
		self::share_methods();
		self::$routes = self::routes();
		
		self::$page = null;
		$url = '/' . trim( self_url(), '/' );
		
		$page_list = array_reindex( self::$site['page'], 'page_path' );
		
		foreach ( self::$routes as $route_rule => $route_item )
		{
			if ( preg_match( $route_item['pattern'], $url, $route_match ) )
			{
				$route_params = array();
				
				foreach ( $route_item['params'] as $route_name => $route_item )
					if ( is_numeric( $route_item ) )
						$route_params[$route_name] = trim( $route_match[$route_item], '/' );
					else
						$route_params[$route_name] = trim( $route_item, '/' );
				
				if ( isset( $route_params['controller'] ) && isset( $page_list['/' . $route_params['controller']] ) )
				{
					self::$route_params = $route_params;
					self::$page = $page_list['/' . $route_params['controller']];
					
					break;
				}
			}
		}
		
		if ( is_null( controller() ) )
			not_found();
		
		if ( isset( self::$page['page_redirect'] ) )
		{
			if ( action() == 'index' )
				redirect_to( self::$page['page_redirect'] );
			else
				not_found();
		}
		
		self::set_cache_mode();
		
		if ( isset( self::$site['lang'] ) )
			if ( preg_match( '/^(\w+)\/?/', controller(), $match ) &&
					in_array( $match[1], array_keys( self::$lang_list ) ) )
				self::$lang = $match[1];
		
		$layout_view = new view();
		
		$layout_view -> assign( array(
			'meta_title' => self::$page['meta_title'],
			'meta_keywords' => self::$page['meta_keywords'],
			'meta_description' => self::$page['meta_description'] ) );
		
		if ( isset( self::$page['block'] ) )
		{
			foreach ( self::$page['block'] as $block )
			{
				$module_params = array();
				if ( isset( $block['param'] ) )
					foreach ( $block['param'] as $param )
						$module_params[$param['param_name']] = $param['param_value'];
				
				$module_name = $block['module_name'];
				$module_main = (boolean) $block['area_main'];
				$module_action = $module_main ? action() :
					( ( isset( $module_params['action'] ) && $module_params['action'] ) ? $module_params['action'] : 'index' );
				
				try
				{
					if ( controller() == 'admin' )
						$module_object = admin::factory( object() );
					else
						$module_object = module::factory( $module_name );
					
					$module_object -> init( $module_action, $module_params, $module_main );
					
					$layout_view -> assign( $block['area_name'], $module_object -> get_content() );
					if ( $module_main )
						$layout_view -> assign( $module_object -> get_output() );
				}
				catch ( Exception $e )
				{
					$error_view = new view();
					$error_view -> assign( 'message', $e -> getMessage() );
					
					if ( DEBUG_MODE )
					{
						$error_view -> assign( 'line', $e -> getLine() );
						$error_view -> assign( 'file', $e -> getFile() );
						$error_view -> assign( 'trace', $e -> getTraceAsString() );
					}
					
					$error_content = $error_view -> fetch( 'block/error.tpl' );
					
					if ( $e -> getCode() )
						$layout_view -> assign( $block['area_name'], $error_content );
					else
						die( $error_content );
				}
			}
		}
		
		$layout_view -> display( self::$page['page_layout'] . '.tpl' );
	}
	
	public static function routes()
	{
		include_once APP_DIR . '/config/routes.php';
		
		$routes = array_merge( $routes, array(
			'/admin/@object' => array(
				'controller' => 'admin',
				'object' => '\w+',
			),
			'/admin/@object/@action' => array(
				'controller' => 'admin',
				'object' => '\w+',
			),
			'/admin/@object/@action/@id' => array(
				'controller' => 'admin',
				'object' => '\w+',
			),
			
			'@controller' => array(),
			'@controller/@action' => array(),
			'@controller/@action/@id' => array() ) );
		
		$route_map = array();
		
		foreach ( $routes as $route_rule => $route_rule_params )
		{
			$route_pattern = $route_rule;
			$route_pattern_params = $route_rule_params;
			
			if ( preg_match_all( '/@\w+/i', $route_rule, $route_match ) )
			{
				foreach ( $route_match[0] as $route_index => $route_name )
				{
					if ( $route_name == '@controller' )
					{
						$route_pattern = preg_replace( '/@controller/', '([/\w]*)', $route_pattern );
						$route_pattern_params['controller'] = $route_index + 1;
					}
					else if ( $route_name == '@action' )
					{
						$route_pattern = preg_replace( '/@action/', '(\w*)', $route_pattern );
						$route_pattern_params['action'] = $route_index + 1;
					}
					else if ( $route_name == '@id' )
					{
						$route_pattern = preg_replace( '/@id/', '(\d*)', $route_pattern );
						$route_pattern_params['id'] = $route_index + 1;
					}
					else
					{
						$route_var_name = preg_replace( '/@/', '', $route_name );
						
						if ( isset( $route_rule_params[$route_var_name] ) )
						{
							$route_pattern = preg_replace( '/' . $route_name . '/', '(' . $route_rule_params[$route_var_name] . ')', $route_pattern );
							$route_pattern_params[$route_var_name] = $route_index + 1;
						}
					}
				}
			}
			
			if ( !isset( $route_pattern_params['controller'] ) )
				$route_pattern_params['controller'] = '';
			if ( !isset( $route_pattern_params['action'] ) )
				$route_pattern_params['action'] = 'index';
			
			$route_pattern = '|^' . $route_pattern . '$|i';
			
			$route_map[$route_rule] = array( 'pattern' => $route_pattern, 'params' => $route_pattern_params );
		}
		
		return $route_map;
	}
	
	// Метод устанавливает параметры кеширования и сохраняет их в сессии
	public static function set_cache_mode()
	{
		if ( !isset( $_SESSION['_cache_mode'] ) )
			$_SESSION['_cache_mode'] = SITE_CACHE;
		
		if ( isset( $_REQUEST['cache_on'] ) ) {
			$_SESSION['_cache_mode'] = true;
			redirect_to( request_url( array(), array( 'cache_on' ) ) );
		}
		
		if ( isset( $_REQUEST['cache_off'] ) ) {
			$_SESSION['_cache_mode'] = false;
			redirect_to( request_url( array(), array( 'cache_off' ) ) );
		}
		
		if ( isset( $_REQUEST['cache_clear'] ) ) {
			cache::clear();
			redirect_to( request_url( array(), array( 'cache_clear' ) ) );
		}
		
		self::$cache_mode = $_SESSION['_cache_mode'];
	}
	
	public static function get_param( $param_name, $param_value = null )
	{
		if ( isset( self::$route_params[$param_name] ) )
			return self::$route_params[$param_name];
		else
			return $param_value;
	}
	
	public static function get_routes()
	{
		return self::$routes;
	}
	
	public static function controller()
	{
		return self::get_param( 'controller' );
	}
	
	public static function action()
	{
		return self::get_param( 'action', 'index' );
	}
	
	public static function id()
	{
		return self::get_param( 'id', '' );
	}
	
	public static function object()
	{
		return self::get_param( 'object' );
	}
	
	public static function is_cache()
	{
		return self::$cache_mode;
	}
	
	public static function lang()
	{
		return self::$lang;
	}
	
	public static function lang_list()
	{
		return self::$lang_list;
	}
	
	public static function page()
	{
		return self::$page;
	}
	
	public static function site()
	{
		return self::$site;
	}
	
	// Метод создает общедоступные алиасы для некоторых методов класса
	public static function share_methods()
	{
		$methods = array( 'get_param', 'get_routes', 'controller', 'action', 'id', 'object', 'is_cache', 'lang', 'lang_list', 'page', 'site' );
		
		foreach ( $methods as $method )
			if ( !is_callable( $method ) && method_exists( 'System', $method ) )
				eval( 'function ' . $method . '() { $args = func_get_args(); return call_user_func_array( array( "System", "' . $method . '" ), $args ); }' );
	}
}
