<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('resize_image'))
{
	function resize_image($file, $w, $h, $filename, $crop=FALSE, $ext=null, $upload_path='') {
	    list($width, $height) = getimagesize($upload_path.$file);
	    $r = $width / $height;
	    if ($crop) {
	        if ($width > $height) {
	            $width = ceil($width-($width*abs($r-$w/$h)));
	        } else {
	            $height = ceil($height-($height*abs($r-$w/$h)));
	        }
	        $newwidth = $w;
	        $newheight = $h;
	    } else {
            $newheight = $h/2;
            $newwidth = $w/2;
	    }
		if ($ext == 'png') {
			$src = @imagecreatefrompng($upload_path.$file);
		} else {
	    	$src = imagecreatefromjpeg($upload_path.$file);
		}
	    $dst = imagecreatetruecolor($newwidth, $newheight);
	    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	    $resizedFilePath = $upload_path."/upload/thumbnail_".$filename.".jpeg";

	    imagejpeg($dst, $resizedFilePath);

	    imagedestroy($src);
	    imagedestroy($dst);

	    return $resizedFilePath;
	}
}

if ( ! function_exists('resize_image_tmp'))
{
	function resize_image_tmp($file, $w, $h, $filename, $crop=FALSE, $ext=null, $upload_path='') {
	    list($width, $height) = getimagesize($upload_path.$file);
	    $r = $width / $height;
	    if ($crop) {
	        if ($width > $height) {
	            $width = ceil($width-($width*abs($r-$w/$h)));
	        } else {
	            $height = ceil($height-($height*abs($r-$w/$h)));
	        }
	        $newwidth = $w;
	        $newheight = $h;
	    } else {
            $newheight = $h/2;
            $newwidth = $w/2;
	    }
		if ($ext == 'png') {
			$src = @imagecreatefrompng($upload_path.$file);
		} else {
	    	$src = imagecreatefromjpeg($upload_path.$file);
		}
	    $dst = imagecreatetruecolor($newwidth, $newheight);
	    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	    $resizedFilePath = $upload_path."/temp/thumbnail_".$filename.".jpeg";

	    imagejpeg($dst, $resizedFilePath);

	    imagedestroy($src);
	    imagedestroy($dst);

	    return $resizedFilePath;
	}
}

if (!function_exists('_create_preview_images') && extension_loaded('imagick'))
{
	function _create_preview_images($file_name, $file_path) {

		$path_parts = pathinfo($file_path.$file_name);
		//debugarr($path_parts); exit;

		//$file_name = str_ireplace('.pdf', '.pdf', $file_name);

		//$file_name = basename($file_name, '.pdf');
		$imagick = new Imagick();
		$imagick->setResolution(150, 150);

		/*try {
			$imagick->readImage($file_path.$file_name.'.pdf');
		} catch (Exception $e) {
			log_message('error', '.pdf probably in capital letters');
			$imagick->readImage($file_path.$file_name.'.PDF');
		}*/
		$imagick->setColorspace(Imagick::COLORSPACE_SRGB);
		$imagick->readImage($file_path.$file_name);

		//$imagick->setImageBackgroundColor('#ffffff');
		//$imagick->setImageCompression(imagick::COMPRESSION_JPEG);
		//$imagick->setImageCompressionQuality(100);

		//$imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

		foreach ($imagick as $c => $_page) {
		    $_page->setImageBackgroundColor('#ffffff');
		    $_page->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
		    //$_page = $_page->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		    $_page->setImageCompression(imagick::COMPRESSION_JPEG); 
		    $_page->setImageCompressionQuality(100);
		    $_page->setImageFormat('jpg');
		    //$_page->writeImage($file."_background-$c.jpg");

		    $_page->writeImage($file_path.$path_parts['filename'].'-'.$c.'.jpg');
		}

		$imagick->destroy();
	}

}