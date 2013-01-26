<?php
class admin_table_work extends admin_table
{
	protected function action_add_save( $redirect = true )
	{
		$primary_field = parent::action_add_save( false );
		
		$work_title = init_string( 'work_title' );
		if ( is_empty( $work_title ) ) {
			$work_text = init_string( 'work_text' );
			$work_title = $this -> get_title( $work_text );
			db::update( $this -> object, array( 'work_title' => $work_title ),
				array( $this -> primary_field => $primary_field ) );
		}
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_edit_save( $redirect = true )
	{
		parent::action_edit_save( false );
		
		$work_title = init_string( 'work_title' );
		if ( is_empty( $work_title ) ) {
			$work_text = init_string( 'work_text' );
			$work_title = $this -> get_title( $work_text );
			db::update( $this -> object, array( 'work_title' => $work_title ),
				array( $this -> primary_field => id() ) );
		}
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function get_title( $work_text )
	{
		$work_text_list = explode( "\n", $work_text );
		$work_title = trim( $work_text_list[0], " .,…;:!?\r\n-–" );
		
		return "\"{$work_title}...\"";
	}
}
