<?php
class module_portfolio extends module
{
	// Вывод списка фотографий
	protected function action_index()
	{
		$rows_per_page = max( intval( $this -> get_param( 'rows_per_page_photo' ) ), 1 );
		$cols_per_page = max( intval( $this -> get_param( 'cols_per_page_photo' ) ), 1 );
		
		$items_per_page = $rows_per_page * $cols_per_page;
		
		$portfolio_query = 'select count(*) as _portfolio_count from portfolio where portfolio_active = 1';
		$portfolio_count = db::select_row( $portfolio_query );
		
		if ( $portfolio_count = $portfolio_count['_portfolio_count'] )
		{
			$pages = paginator::construct( $portfolio_count, array( 'by_page' => $items_per_page ) );
			
			$portfolio_query = 'select * from portfolio where portfolio_active = 1
				order by portfolio_order limit ' . $pages['by_page'] . ' offset ' . $pages['offset'];
			$portfolio_list = db::select_all( $portfolio_query );
			
			$portfolio_table = array();
			for( $i = 0; $i < ceil( count( $portfolio_list ) / $cols_per_page ); $i++ )
				for( $j = 0; $j < $cols_per_page; $j++ )
					if ( isset( $portfolio_list[$i * $cols_per_page + $j] ) )
						$portfolio_table[$i][$j] = $portfolio_list[$i * $cols_per_page + $j];
					else 
						$portfolio_table[$i][$j] = array();
								
			$this -> view -> assign( 'portfolio_table', $portfolio_table );
			$this -> view -> assign( 'table_cell_width', round( 100 / $cols_per_page ) );
			
			$this -> view -> assign( 'pages', paginator::fetch( $pages ) );
		}
		
		$this -> content = $this -> view -> fetch( 'module/portfolio/list.tpl' );
	}
}
