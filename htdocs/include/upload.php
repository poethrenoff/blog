<?php
class upload
{
	public static function upload_file( $file_descr, $upload_path, $only_image = false )
	{
		umask( 0 );
		
		$name = $file_descr['name']; $tmp_name = $file_descr['tmp_name'];
		$real_upload_path = $_SERVER['DOCUMENT_ROOT'] . $upload_path;
		
		if ( $file_descr['error'] != UPLOAD_ERR_OK )
			throw new Exception( 'Ошибка. Невозможно закачать файл "' . $name . '".', true );
		
		if ( $only_image && ( getImageSize( $tmp_name ) === false ) )
			throw new Exception( 'Ошибка. Файл "' . $name . '" не является изображением.', true );
		
		if( !file_exists( $real_upload_path ) )
			if ( !( @mkdir( $real_upload_path , 0777, true ) ) )
				throw new Exception( 'Ошибка. Невозможно создать каталог "' . $real_upload_path . '".', true );
		
		$name = self::get_unique_file_name( $real_upload_path, self::get_translit_file_name( $name ) );
		
		if ( !( @move_uploaded_file( $tmp_name, $real_upload_path . $name ) &&
				@chmod( $real_upload_path . $name, 0777 ) ) )
			throw new Exception( 'Ошибка. Невозможно закачать файл "' . $real_upload_path . $name . '".', true );
		
		return $upload_path . $name;
	}
	
	public static function get_unique_file_name( $path, $name )
	{
		$point_index = strrpos( $name, '.' );
		$base = ( $point_index !== false ) ? substr( $name, 0, $point_index ) : $name;
		$ext = ( $point_index !== false ) ? substr( $name, $point_index, strlen( $name ) ) : '';
		
		$new_name = $name; $n = 0;
		while ( file_exists( $path . '/' . $new_name ) )
			$new_name = $base . '_' . ( ++$n ) . $ext;
		return $new_name;
	}
	
	public static function get_translit_file_name( $name )
	{
		return preg_replace( '/[^\w\.\[\]-]/u', '', strtr( mb_strtolower( $name, 'UTF-8' ), self::$translit ) );
	}
	
	public static $translit = array(
		' ' => '_', 'ё' => 'yo', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
		'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh',
		'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'u', 'я' => 'ya', '№' => 'N' );
}
