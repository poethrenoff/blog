<?php
// Инициализация строковой переменной
function init_string( $varname, $vardef = '' )
{
	if ( isset( $_REQUEST[$varname] ) )
		return (string) $_REQUEST[$varname];
	else
		return (string) $vardef;
}

// Инициализация массива
function init_array( $varname, $vardef = array() )
{
	if ( isset( $_REQUEST[$varname] ) && is_array( $_REQUEST[$varname] ) )
		return (array) $_REQUEST[$varname];
	else
		return (array) $vardef;
}

// Инициализация переменной из сессии
function init_session( $varname, $vardef = '' )
{
	if ( isset( $_SESSION[$varname] ) )
		return $_SESSION[$varname];
	else
		return $vardef;
}

// Инициализация переменной из куков
function init_cookie( $varname, $vardef = '' )
{
	if ( isset( $_COOKIE[$varname] ) )
		return (string) $_COOKIE[$varname];
	else
		return (string) $vardef;
}

function array_reindex( $array, $key1 = '', $key2 = '', $key3 = '', $key4 = '' )
{
	$reverted_array = array();
	
	if ( is_array( $array ) )
	{
		foreach( $array as $item )
		{
			if ( !$key1 )
				$reverted_array[$item] = $item;
			else if ( !$key2 )
				$reverted_array[$item[$key1]] = $item;
			else if ( !$key3 )
				$reverted_array[$item[$key1]][$item[$key2]] = $item;
			elseif ( !$key4 )
				$reverted_array[$item[$key1]][$item[$key2]][$item[$key3]] = $item;
			else
				$reverted_array[$item[$key1]][$item[$key2]][$item[$key3]][$item[$key4]] = $item;
		}
	}
	
	return $reverted_array;
}

function array_group( $array, $key )
{
	$grouped_array = array();
	
	if ( is_array( $array ) )
		foreach ( $array as $item )
			$grouped_array[$item[$key]][] = $item;
	
	return $grouped_array;
}

function array_make_in( $array, $key = '', $quote = false )
{
	$in = '0'; $ids = array();
	
	if ( is_array( $array ) )
	{
		foreach ( $array as $record )
			$ids[] = $quote ? ( $key ? addslashes( $record[$key] ) : addslashes( $record ) ) :
				( $key ? intval( $record[$key] ) : intval( $record ) );
		
		if ( count( $ids ) )
			$in = $quote ? ( "'" . join( "', '", $ids ) . "'" ) : join( ", ", $ids );
	}
	
	return $in;
}

function get_translate_clause( $table_name, $field_name, $table_record, $record_lang, $field_title = null )
{
	if ( is_null( $field_title ) )
		$field_title = $field_name;
	
	return "(
		select
			record_value
		from
			translate, lang
		where
			translate.table_record = {$table_record} and 
			translate.table_name = '{$table_name}' and
			translate.field_name = '{$field_name}' and
			lang.lang_id = translate.record_lang and
			lang.lang_name = '{$record_lang}'
	) as {$field_title}";
}

function get_translate_values( $table_name, $field_name, $table_record, $record_lang = null )
{
	$translate_values = db::select_all( '
		select lang.lang_name, translate.record_value
		from translate left join lang on lang.lang_id = translate.record_lang
		where table_name = :table_name and field_name = :field_name and table_record = :table_record
		order by lang.lang_default desc',
	array( 'table_name' => $table_name, 'field_name' => $field_name, 'table_record' => $table_record ) );
	
	$record_values = array_reindex( $translate_values, 'lang_name' );
	
	if ( !is_null( $record_lang ) )
		return $record_values[$record_lang];
	
	return $record_values;
}

function get_preference( $preference_name, $default_value = '' )
{
	if ( defined( $preference_name ) )
		return constant( $preference_name );
	else
		return $default_value;
}

function build()
{
	$page_list = db::select_all( 'select * from page, layout where page_layout = layout_id and page_active = 1 order by page_order' );
	$page_list = array_reindex( tree::get_tree( $page_list, 'page_id', 'page_parent' ), 'page_id' );
	
	$area_list = db::select_all( 'select * from layout_area order by area_order' );
	$area_list = array_group( $area_list, 'area_layout' );
	
	$block_list = db::select_all( 'select * from block, module where block_module = module_id' );
	$block_list = array_reindex( $block_list, 'block_page', 'block_area' );
	
	$block_param_list = db::select_all( 'select * from block_param, module_param where param = param_id' );
	$block_param_list = array_group( $block_param_list, 'block' );
	
	$param_value_list = db::select_all( 'select * from param_value' );
	$param_value_list = array_reindex( $param_value_list, 'value_id' );
	
	$site = array();
	
	foreach( $page_list as $page )
	{
		$site_page = array();
		
		$page_path = array( $page['page_name'] ); $page_parent = $page['page_id'];
		while ( $page_parent = $page_list[$page_parent]['page_parent'] )
			$page_path[] = $page_list[$page_parent]['page_name'];
		
		$page_path = array_reverse( $page_path ); array_shift( $page_path );
		
		$site_page['page_id'] = $page['page_id'];
		$site_page['page_path'] = '/' . join( '/', $page_path );
		
		if ( $page['page_folder'] )
		{
			$page_redirect = db::select_row( 'select * from page where page_parent = :page_parent and page_active = 1 order by page_order',
				array( 'page_parent' => $page['page_id'] ) );
			
			if ( $page_redirect )
				$site_page['page_redirect'] = rtrim( $site_page['page_path'] , '/' ) . '/' . $page_redirect['page_name'];
			else
				continue;
		}
		else
		{
			$site_page['page_layout'] = $page['layout_name'];
			
			$site_page['meta_title'] = $page['meta_title'];
			$site_page['meta_keywords'] = $page['meta_keywords'];
			$site_page['meta_description'] = $page['meta_description'];
			
			if ( isset( $area_list[$page['layout_id']] ) )
			{
				foreach( $area_list[$page['layout_id']] as $area )
				{
					if ( isset( $block_list[$page['page_id']][$area['area_id']] ) )
					{
						$block = $block_list[$page['page_id']][$area['area_id']];
						
						$page_block = array();
						
						$page_block['area_name'] = $area['area_name'];
						$page_block['area_main'] = $area['area_main'] ? 1 : 0;
						$page_block['module_name'] = $block['module_name'];
						
						if ( isset( $block_param_list[$block['block_id']] ) )
						{
							foreach( $block_param_list[$block['block_id']] as $param )
							{
								$block_param = array();
								
								$block_param['param_name'] = $param['param_name'];
								if ( $param['param_type'] == 'select' )
									$block_param['param_value'] = isset( $param_value_list[$param['value']] ) ?
										$param_value_list[$param['value']]['value_content'] : '';
								else
									$block_param['param_value'] = $param['value'];
								
								$page_block['param'][] = $block_param;
							}
						}
						
						$site_page['block'][] = $page_block;
					}
				}
			}
		}
		
		$site['page'][] = $site_page;
	}
	
	$site['page'][] = array ( 'page_id' => '', 'page_path' => '/admin', 'page_layout' => 'admin', 
		'meta_title' => '', 'meta_keywords' => '', 'meta_description' => '', 'block' => array(
			array ( 'area_name' => 'content', 'area_main' => 1, 'module_name' => 'admin', 'param' => array(
				array( 'param_name' => 'action', 'param_value' => 'index' ) ) ),
			array ( 'area_name' => 'menu', 'area_main' => 0, 'module_name' => 'admin', 'param' => array(
				array( 'param_name' => 'action', 'param_value' => 'menu' ) ) ),
			array ( 'area_name' => 'auth', 'area_main' => 0, 'module_name' => 'admin', 'param' => array(
				array( 'param_name' => 'action', 'param_value' => 'auth' ) ) ) ) );
	
	if ( isset( metadata::$objects['lang'] ) )
	{
		$lang_list = db::select_all( 'select * from lang order by lang_default desc' );
		
		foreach ( $lang_list as $lang )
		{
			$site_lang = $lang;
			
			$dictionary = db::select_all( "
				select
					dictionary.word_name, translate.record_value
				from
					dictionary, translate
				where
					translate.table_record = dictionary.word_id and 
					translate.table_name = 'dictionary' and
					translate.field_name = 'word_value' and
					translate.record_lang = :lang_id", array( 'lang_id' => $lang['lang_id'] ) );
			
			foreach ( $dictionary as $word )
				$site_lang['dictionary'][] = array( 'word_name' => $word['word_name'], 'word_value' => $word['record_value'] );
			
			$site['lang'][] = $site_lang;
		}
	}
	
	if ( isset( metadata::$objects['preference'] ) )
	{
		$preference_list = db::select_all( 'select * from preference' );
		
		foreach ( $preference_list as $preference )
			$site['preference'][] = array( 'preference_name' => $preference['preference_name'], 'preference_value' => $preference['preference_value'] );
	}
	
	cache::set( CONFIG_FILE, $site );
	
	return $site;
}

function url_for( $url_array = array(), $url_host = '' )
{
	if ( !is_array( $url_array ) || count( $url_array ) == 0 )
		return $_SERVER['REQUEST_URI'];
	
	if ( !isset( $url_array['action'] ) )
		$url_array['action'] = !isset( $url_array['controller'] ) ? action() : 'index';
	if ( !isset( $url_array['controller'] ) )
		$url_array['controller'] = controller();
	
	$routes = get_routes();
	
	$most_match_rule = ''; $most_match_count = 0;
	foreach ( $routes as $route_rule => $route_item )
	{
		if ( count( array_diff_key( $route_item['params'], $url_array ) ) == 0 )
		{
			$is_match = true;
			foreach ( $route_item['params'] as $route_param_name => $route_param_value )
				if ( !is_numeric( $route_param_value ) )
					$is_match &= $url_array[$route_param_name] === $route_param_value;
			
			if ( $is_match ) {
				$match_count = count( array_intersect_key( $route_item['params'], $url_array ) );
				if ( $match_count > $most_match_count ) {
					$most_match_count = $match_count; $most_match_rule = $route_rule;
				}
			}
		}
	}
	
	$url = $most_match_rule;
	if ( $url_array['action'] == 'index' )
		$url = preg_replace( '|/@action$|i', '', $url );
	
	foreach ( $routes[$most_match_rule]['params'] as $route_param_name => $route_param_value )
		$url = preg_replace( '/@' . $route_param_name . '/', $url_array[$route_param_name], $url );
	
	$query_string = http_build_query( prepare_query( $url_array, array_keys( $routes[$most_match_rule]['params'] ) ) );
	
	$url = $url_host . '/' . trim( $url, '/' ) . ( $query_string ? '?' . $query_string : '' );
	
	return $url;
}

function redirect_to( $url_array = array() )
{
	if ( !is_array( $url_array ) )
		$location = $url_array;
	else
		$location = url_for( $url_array );
	
	header( 'Location: ' . $location );
	
	exit;
}

function redirect_back()
{
	$back_url = '/';
	
	if ( isset( $_SERVER['HTTP_REFERER'] ) && strstr( $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'] ) )
		$back_url = $_SERVER['HTTP_REFERER'];
	
	redirect_to( $back_url );
}

function prepare_query( $include = array(), $exclude = array() )
{
	foreach ( $include as $var_name => $var_value )
		if ( in_array( $var_name, $exclude ) || is_empty( $var_value ) )
			unset( $include[$var_name] );
	
	return $include;
}

function self_url( $include = array(), $exclude = array() )
{
	$self_url = preg_replace( '/\?.*$/', '', $_SERVER['REQUEST_URI'] );
	
	$query_string = http_build_query( prepare_query( $include, $exclude ) );
	
	return $self_url . ( $query_string ? '?' . $query_string : '' );
}

function request_url( $include = array(), $exclude = array() )
{
	return self_url( array_merge( $_GET, $include ), $exclude );
}

function not_found()
{
	redirect_to( '/' );
}

function h( $s )
{
	return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' );
}

function is_empty( $s )
{
	return trim( $s ) === '';
}

function declOfNum( $number, $titles, $view_number = true )
{
	$cases = array( 2, 0, 1, 1, 1, 2 );
	return ( $view_number ? $number . ' ' : '' ) . $titles[( $number % 100 > 4 && $number % 100 < 20 ) ? 2 : $cases[min( $number % 10, 5 )]];
}

function generate_key( $max = 128 )
{
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$len = strlen( $chars ) - 1; $password = '';
	while ( $max-- )
		$password .= $chars[rand( 0, $len )];
	return $password;
}

function get_probability( $percent )
{
	return mt_rand( 0, mt_getrandmax() ) < $percent * mt_getrandmax() / 100;
}

function delete_directory( $dir )
{
	if ( !file_exists( $dir ) )
		return true;
	if ( !is_dir( $dir ) )
		return unlink( $dir );
	
	foreach ( scandir( $dir ) as $item )
	{
		if ( $item == '.' || $item == '..' )
			continue;
		if ( !delete_directory( $dir . DIRECTORY_SEPARATOR . $item ) )
			return false;
	}
	
	return rmdir( $dir );
}

function strip_tags_attributes( $string, $allowtags = null, $allowattributes = null )
{ 
	$string = strip_tags( $string, $allowtags ); 
	
	if ( !is_null( $allowattributes ) )
	{ 
		if ( !is_array( $allowattributes ) ) 
			$allowattributes = explode( ',', $allowattributes ); 
		if ( is_array( $allowattributes ) ) 
			$allowattributes = implode( ')(?<!', $allowattributes );
		if ( strlen( $allowattributes ) > 0 ) 
			$allowattributes = '(?<!' . $allowattributes . ')'; 
		$string = preg_replace_callback( '/<[^>]*>/i', create_function( 
			'$matches', 'return preg_replace("/ [^ =]*' . $allowattributes .
				'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);' ), $string ); 
	}
	
	return $string; 
}

function global_autoload( $class_name )
{
	$class_path = str_replace( '_', DIRECTORY_SEPARATOR, $class_name );
	
	if ( file_exists( $class_file = CLASS_DIR . $class_path . '.php' ) )
		include_once( $class_file );
}

spl_autoload_register( 'global_autoload' );

system::init();