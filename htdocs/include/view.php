<?php
include_once 'Smarty/Smarty.class.php';

class view extends Smarty
{
	public function __construct()
	{
		parent::__construct();
		
		$this -> caching = false;
		$this -> compile_dir = CACHE_DIR;
		$this -> template_dir = VIEW_DIR;
	}
	
	public function fetch( $template, $cache_id = null, $compile_id = null, $parent = null )
	{
		try {
			return parent::fetch( $template, $cache_id, $compile_id, $parent );
		}
		catch ( Exception $e ) {
			throw new Exception( $e -> getMessage(), true );
		}
	}
}