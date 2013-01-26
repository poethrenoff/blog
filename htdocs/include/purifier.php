<?php
include_once 'HTMLPurifier/HTMLPurifier.auto.php';

class purifier extends HTMLPurifier
{
	protected static $instance = null;
	
	public static function clear( $html )
	{
		if ( is_null( self::$instance ) )
		{
			$config = HTMLPurifier_Config::createDefault();
			
			$config -> set( 'Core.Encoding', 'UTF-8' );
			$config -> set( 'HTML.Doctype', 'XHTML 1.0 Strict' );
			$config -> set( 'Cache.DefinitionImpl', null );
			
			$config -> set( 'AutoFormat.RemoveEmpty', true );
			$config -> set( 'AutoFormat.RemoveEmpty.RemoveNbsp', true );
			
			$config -> set( 'HTML.SafeObject', true );
			$config -> set( 'HTML.SafeEmbed', true );
			
			$config -> set( 'HTML.AllowedAttributes', 'style,href,src,width,height,alt,title,name,value,type' );
			$config -> set( 'HTML.AllowedElements', 'p,div,span,br,i,b,sub,sup,pre,address,em,strong,ul,ol,li,h1,h2,h3,h4,h5,a,img,object,param,embed' );
			
			self::$instance = new HTMLPurifier( $config );
		}
		
		return self::$instance -> purify( $html );
	}
}
