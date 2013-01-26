<?php
class admin_table_news extends admin_table
{
	/**
	 * Имя поля "Теги" в форме редактирования записи
	 */
	protected $tag_field = 'tag_string';
	
	/**
	 * Сохраняем теги после добавления записи
	 */
	protected function action_add_save( $redirect = true )
	{
		$primary_field = parent::action_add_save( false );
		
		$this -> set_tags( $primary_field, init_string( $this -> tag_field ) );
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	/**
	 * Сохраняем теги после изменения записи
	 */
	protected function action_edit_save( $redirect = true )
	{
		parent::action_edit_save( false );
		
		$this -> set_tags( id(), init_string( $this -> tag_field ) );
		
		if ( $redirect )
			$this -> redirect();
	}
	
	/**
	 * Добавляем в метаданные поле "Теги"
	 */
	protected function record_card( $action = 'edit' )
	{
		$this -> fields = array( 'news_id' => $this -> fields['news_id'],
			'news_content' => $this -> fields['news_content'],
			$this -> tag_field =>
				array( 'title' => 'Теги', 'type' => 'string', 'errors' => '', 'errors_code' => 0 ),
			'news_date' => $this -> fields['news_date'],
			'news_publish' => $this -> fields['news_publish'],
			'news_active' => $this -> fields['news_active'] );
		
		parent::record_card( $action );
	}
	
	/**
	 * Заполняем поле "Теги" в форме редактирования записи
	 */
	protected function get_record( $primary_field = '' )
	{
		$record = parent::get_record( $primary_field );
		
		$record[$this -> tag_field] = $this -> get_tags( $record[$this -> primary_field] );
		
		return $record;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Метод обработки строки тегов
	 */
	public function prepare_tags( $tag_string )
	{
		$tag_array = array();
		
		$tag_items = array_unique( array_map( 'trim', explode( ',', trim( $tag_string ) ) ) );
		
		foreach ( $tag_items as $tag )
		{
			if ( $tag === '' ) continue;
			
			$tag_item = db::select_row( 'select * from tag where lower( tag_title ) = lower( :tag_title )',
				array( 'tag_title' => $tag ) );
			
			if ( $tag_item )
			{
				$tag_array[] = $tag_item['tag_id'];
			}
			else
			{
				db::insert( 'tag', array( 'tag_title' => $tag ) );
				
				$tag_array[] = db::last_insert_id();
			}
		}
		
		return $tag_array;
	}
	
	/**
	 * Метод извлечения из базы тегов текущей записи
	 */
	public function get_tags( $primary_field )
	{
		$tag_list = db::select_all( '
			select tag.tag_title from tag, news_tag
			where tag.tag_id = news_tag.tag_id and
				news_tag.news_id = :news_id
			order by tag.tag_title',
			array( 'news_id' => $primary_field ) );
		
		$tag_array = array();
		foreach ( $tag_list as $tag_row )
			$tag_array[] = $tag_row['tag_title'];
		
		return join( ', ', $tag_array );
	}
	
	/**
	 * Метод сохранения в базе тегов текущей записи
	 */
	public function set_tags( $primary_field, $tag_string )
	{
		db::delete( 'news_tag', array( 'news_id' => $primary_field ) );
		
		$tag_array = $this -> prepare_tags( $tag_string );
		foreach ( $tag_array as $tag_id )
			db::insert( 'news_tag', array( 'tag_id' => $tag_id, 'news_id' => $primary_field ) );
	}
}
