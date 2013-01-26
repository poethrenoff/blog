<?php
class module_news extends module
{
	// Разрешен просмотр только опубликованных новостей
	protected $only_publish = false;
	
	// Инициализация модуля
	public function init( $action = 'index', $params = array(), $is_main = false )
	{
		$this -> only_publish = init_cookie( 'view' ) != 'all';
		
		parent::init( $action, $params, $is_main );
	}
	
	// Вывод списка новостей
	protected function action_index()
	{
		$news_by_page = $this -> get_param( 'news_by_page' );
		
		$news_count = db::select_cell( 'select count(*) from news where news_active = 1 ' .
			( $this -> only_publish ? 'and news_publish = 1' : '' ) );
		
		if ( $news_count )
		{
			$pages = paginator::construct( $news_count, array( 'by_page' => $news_by_page ) );
			
			$news_list = db::select_all( '
				select news.*, count( comment_id ) as _comment_count
				from news left join comment on news_id = comment_news
				where news_active = 1 ' . ( $this -> only_publish ? 'and news_publish = 1' : '' ) . '
				group by news_id order by news_date desc limit ' . $pages['by_page'] . ' offset ' . $pages['offset'] );
			
			$news_list_in = array();
			foreach ( $news_list as $news_index => $news_item )
				$news_list_in[] = $news_item['news_id'];
			$news_list_in = count( $news_list_in ) ? join( ', ', $news_list_in ) : 0;
			
			$tag_list = $this -> get_tag_list( $news_list_in );
			
			foreach ( $news_list as $news_index => $news_item )
			{
				$news_list[$news_index]['news_date'] = date::get( $news_item['news_date'], 'd.m.Y H:i' );
				
				if ( substr( $news_list[$news_index]['news_date'], 11 ) == '00:00' )
					$news_list[$news_index]['news_date'] = substr( $news_list[$news_index]['news_date'], 0, 10 );
				
				$news_list[$news_index]['news_url'] =
					url_for( array( 'controller' => '', 'action' => 'post', 'id' => $news_item['news_id'] ) );
				$news_list[$news_index]['comment_url'] = $news_list[$news_index]['news_url'] . '?mode=reply';
				
				$news_list[$news_index]['tag_list'] = isset( $tag_list[$news_item['news_id']] ) ? $tag_list[$news_item['news_id']] : array();
			}
			
			$this -> view -> assign( 'news_list', $news_list );
			$this -> view -> assign( 'pages', paginator::fetch( $pages ) );
		}
		
		$this -> content = $this -> view -> fetch( 'module/news/list.tpl' );
	}
	
	// Вывод карточки новости
	protected function action_post()
	{
		$news_query = 'select * from news where news_id = :news_id and news_active = 1 ' .
			( $this -> only_publish ? 'and news_publish = 1' : '' );
		$news_item = db::select_row( $news_query, array( 'news_id' => id() ) );
		
		if ( !$news_item ) return false;
		
		$error = ( init_string( 'action' ) == 'comment' ) ? $this -> add_comment( id() ) : '';
		
		if ( init_string( 'mode' ) == 'reply' )
			$news_item['show_form'] = intval( init_string( 'comment_parent' ) );
		
		$news_item['form_url'] = self_url();
		$news_item['news_date'] = date::get( $news_item['news_date'], 'd.m.Y H:i' );
		
		if ( substr( $news_item['news_date'], 11 ) == '00:00' )
			$news_item['news_date'] = substr( $news_item['news_date'], 0, 10 );
		
		$tag_list = $this -> get_tag_list( $news_item['news_id'] );
		$news_item['tag_list'] = isset( $tag_list[$news_item['news_id']] ) ? $tag_list[$news_item['news_id']] : array();
		
		$comment_query = 'select * from comment where comment_news = :comment_news order by comment_date';
		$comment_list = db::select_all( $comment_query, array( 'comment_news' => id() ) );
		
		foreach ( $comment_list as $comment_index => $comment_item )
			$comment_list[$comment_index]['comment_date'] = date::get( $comment_item['comment_date'], 'd.m.Y H:i' );
		
		$comment_tree = tree::get_tree( $comment_list, 'comment_id', 'comment_parent' );
		
		$this -> view -> assign( $news_item );
		$this -> view -> assign( 'error', $error );
		
		$this -> view -> assign( 'comment_author', init_string( 'comment_author' ) ?
			init_string( 'comment_author' ) : init_cookie( 'author' ) );
		$this -> view -> assign( 'comment_content', init_string( 'comment_content' ) );
		
		$this -> view -> assign( 'comment_tree', $comment_tree );
		
		$this -> content = $this -> view -> fetch( 'module/news/post.tpl' );
	}
	
	// Добавление комментария
	protected function add_comment( $comment_news )
	{
		$comment_parent = init_string( 'comment_parent' );
		
		$comment_author = init_string( 'comment_author' );
		$comment_content = init_string( 'comment_content' );
		
		$captcha_value = init_string( 'captcha_value' );
		
		if ( !$comment_author )
			return 'Ошибка! Не заполнено поле "ИМЯ"!';
		if ( !$comment_content )
			return 'Ошибка! Не заполнено поле "КОММЕНТАРИЙ"!';
		if ( !$captcha_value )
			return 'Ошибка! Не заполнено поле "ЧИСЛО"!';
		
		if ( !captcha::check( $captcha_value ) )
			return 'Ошибка! Введеное число не соответствует коду на изображении!';
		
		$comment_info = $_SERVER['REMOTE_ADDR'] . "\n" . $_SERVER['HTTP_USER_AGENT'];
		$comment_date = date( 'YmdHis', time() );
		
		$comment_content = purifier::clear( $comment_content );
		
		$comment_record = array( 'comment_news' => $comment_news, 'comment_parent' => $comment_parent,
			'comment_content' => $comment_content, 'comment_author' => $comment_author,
			'comment_date' => $comment_date, 'comment_info' => $comment_info );
		
		db::insert( 'comment', $comment_record );
		
		setcookie( 'author', $comment_author, time() + 60 * 60 * 24 * 30, '/' );
		
		$comment_record['comment_date'] = date::get( $comment_record['comment_date'], 'd.m.Y H:i' );
		$comment_record['news_url'] = url_for( array( 'controller' => '', 'action' => 'post', 'id' => $comment_news ),
			'http://' . $_SERVER['HTTP_HOST'] );
		
		$admin_email = get_preference( 'admin_email' );
		$from_email = get_preference( 'from_email' );
		$from_name = get_preference( 'from_name' );
		
		$subject = 'Новый комментарий на "Не дождетесь!"';
		
		$mail_view = new view();
		$mail_view -> assign( $comment_record );
		
		$message = $mail_view -> fetch( 'module/news/mail.tpl' );
		
		sendmail::send( $admin_email, $from_email, $from_name, $subject, $message );
		
		redirect_to( self_url() );
	}
	
	// Экспорт новостей
	protected function action_rss()
	{
		$news_by_page = $this -> get_param( 'news_by_page_rss' );
		
		$news_query = 'select * from news where news_active = 1 ' .
			( $this -> only_publish ? 'and news_publish = 1' : '' ) . '
			order by news_date desc limit ' . $news_by_page;
		$news_list = db::select_all( $news_query );
		
		foreach ( $news_list as $news_index => $news_item )
		{
			$news_list[$news_index]['news_pub_date'] = date::get( $news_item['news_date'], 'r' );
			$news_list[$news_index]['news_date'] = date::get( $news_item['news_date'], 'd.m.Y H:i' );
			
			if ( substr( $news_list[$news_index]['news_date'], 11 ) == '00:00' )
				$news_list[$news_index]['news_date'] = substr( $news_list[$news_index]['news_date'], 0, 10 );
			
			$news_list[$news_index]['news_link'] =
				url_for( array( 'controller' => '', 'action' => 'post', 'id' => $news_item['news_id'] ) );
		}
		
		$this -> view -> assign( 'news_list', $news_list );
		
		header( 'Content-type: text/xml; charset: UTF-8' );
		
		$this -> view -> display( 'module/news/rss.tpl' );
		
		exit;
	}
	
	// Пропус под замок
	protected function action_access()
	{
		setcookie( 'view', 'all', time() + 356 * 60 * 60 * 24, '/' );
		
		header( 'Location: /' );
		
		exit;
	}
	
	protected function get_tag_list( $record_in )
	{
		$tag_list = db::select_all( '
				select news_tag.news_id, tag.tag_title
				from news_tag, tag
				where
					tag.tag_id = news_tag.tag_id and
					news_tag.news_id in ( ' . $record_in . ' )
				order by tag.tag_title' );
		
		foreach ( $tag_list as $tag_index => $tag_item )
			$tag_list[$tag_index]['tag_url'] = 
				url_for( array( 'controller' => 'search', 'tag' => $tag_item['tag_title'] ) );
		
		return array_group( $tag_list, 'news_id' );
	}
}
