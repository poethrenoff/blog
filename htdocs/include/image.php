<?php
class image
{
	public static function process( $action, $original_image, $upload_path, $maxwidth, $maxheight, $transform = array() )
	{
		$real_original_image = $_SERVER['DOCUMENT_ROOT'] . $original_image;
		
		if ( $upload_path )
		{
			$real_upload_path = $_SERVER['DOCUMENT_ROOT'] . $upload_path;
			
			if( !file_exists( $real_upload_path ) )
				if ( !( @mkdir( $real_upload_path , 0777, true ) ) )
					throw new Exception( 'Ошибка. Невозможно создать каталог "' . $real_upload_path . '".', true );
			
			$image_name = upload::get_unique_file_name( $real_upload_path,
				upload::get_translit_file_name( basename( $real_original_image ) ) );
			
			$real_preview_image = $real_upload_path . $image_name;
			
			$preview_image = $upload_path . $image_name;
		}
		else
		{
			$real_preview_image = $real_original_image;
			
			$preview_image = $original_image;
		}
		
		if ( ( $original_image_info = getImageSize( $real_original_image ) ) === false )
			throw new Exception( 'Ошибка. Файл "' . $real_original_image . '" не является изображением.', true );
		
		list( $width, $height, $type, $attr ) = $original_image_info;
		
		switch ( $type )
		{
			case 1: $si = @imageCreateFromGif( $real_original_image ); break;
			case 2: $si = @imageCreateFromJpeg( $real_original_image ); break;
			case 3: $si = @imageCreateFromPng( $real_original_image ); break;
			default:
				throw new Exception( 'Ошибка. Тип изображения "' . $real_original_image . '" не поддерживается.', true );
		}
		
		$ratio = $height / $width - $maxheight / $maxwidth;
		
		switch ( $action )
		{
			case 'resize':
				if ( $ratio > 0 )
				{
					$nwidth = round( $width * $maxheight / $height );
					$nheight = $maxheight;
				}
				else
				{
					$nwidth = $maxwidth;
					$nheight = round( $height * $maxwidth / $width );
				}
				
				$axis_x = 0; $axis_y = 0;
				break;
			
			case 'crop':
				$nwidth = $maxwidth; $nheight = $maxheight;
				
				if ( $ratio > 0 )
				{
					$rheight = $maxheight * $width / $maxwidth;
					$axis_x = 0; $axis_y = round( ( $height - $rheight ) / 2 );
					$height = round( $rheight );
				}
				else
				{
					$rwidth = $maxwidth * $height / $maxheight;
					$axis_x = round( ( $width - $rwidth ) / 2 ); $axis_y = 0;
					$width = round( $rwidth );
				}
				break;
		}
		
		$di = imageCreateTrueColor( $nwidth, $nheight );
		
		imageCopyResampled( $di, $si, 0, 0, $axis_x, $axis_y, $nwidth, $nheight, $width, $height );
		
		foreach ( $transform as $transform_name )
		{
			switch ( $transform_name )
			{
				case 'grayscale':
					for ( $y = 0; $y < $nheight; $y++ ) {
						for ( $x = 0; $x < $nwidth; $x++ ) {
							$gray = ( imageColorAt( $di, $x, $y ) >> 8 ) & 0xFF;
							imageSetPixel ($di, $x, $y, imageColorAllocate( $di, $gray, $gray, $gray ) );
						}
					}
					break;
			}
		}
		
		switch ( $type )
		{
			case 1: @imageGif( $di, $real_preview_image ); break;
			case 2: @imageJpeg( $di, $real_preview_image, 90 ); break;
			case 3: @imagePng( $di, $real_preview_image, 9 ); break;
		}
		
		imageDestroy( $si ); imageDestroy( $di );
		
		if ( !( file_exists( $real_preview_image ) && @chmod( $real_preview_image, 0777 ) ) )
			throw new Exception( 'Ошибка. Невозможно создать файл "' . $real_preview_image . '".', true );
		
		return $preview_image;
	}
}
