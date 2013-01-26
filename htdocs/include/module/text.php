<?php
class module_text extends module
{
	protected function action_index()
	{
		$text_id = $this -> get_param( 'id' );
		
		$this -> view -> assign( array( 'text_content' => self::get_text_by_id( $text_id ) ) );
		
		$this -> content = $this -> view -> fetch( 'module/text/item.tpl' );
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	
	public static function get_text_by_id( $text_id )
	{
		return db::select_cell( 'select text_content from text where text_id = :text_id', array( 'text_id' => $text_id ) );
	}
	
	public static function get_text_by_tag( $text_tag )
	{
		return db::select_cell( 'select text_content from text where text_tag = :text_tag', array( 'text_tag' => $text_tag ) );
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	
	// Дополнительные параметры хэша модуля
	protected function ext_cache_key()
	{
		return parent::ext_cache_key() + array( '_id' => id() );
	}
}