<?php
	ini_set( 'display_errors', true );
	ini_set( 'error_reporting', E_ALL );
	
	setlocale( LC_ALL, 'ru_RU.UTF8' );
	ini_set( 'date.timezone', 'Europe/Moscow' );
	
	define( 'DB_TYPE', 'mysql' ); // mysql, pgsql
	define( 'DB_HOST', 'localhost' );
	define( 'DB_NAME', 'blog' );
	define( 'DB_USER', 'blog' );
	define( 'DB_PASSWORD', 'blog' );
	
	define( 'SITE_TITLE', 'Не дождетесь!' );
	define( 'SITE_CACHE', false );
	
	define( 'DEBUG_MODE', true );
	
	define( 'APP_DIR', dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR );
	
	define( 'CLASS_DIR', APP_DIR . 'include' . DIRECTORY_SEPARATOR );
	define( 'VIEW_DIR', APP_DIR . 'view' . DIRECTORY_SEPARATOR );
	
	define( 'CACHE_DIR', dirname( APP_DIR ) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR );
	define( 'CACHE_TYPE', 'file' ); // file, memory
	define( 'CACHE_TIME', 900 );
	
	define( 'CACHE_HOST', 'localhost' );
	define( 'CACHE_PORT', '11211' );
	
	define( 'CONFIG_FILE', md5( SITE_TITLE ) );
	
	include_once CLASS_DIR . 'include.php';
