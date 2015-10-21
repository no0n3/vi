<?php
namespace components\helpers;

class ImageHelper {
    
    public static function loadImage($source_image_path, $source_image_width = 0, $source_image_height = 0) {

        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);

        $ext = null;

        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $ext = 'jpeg';
                $source_gd_image = imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $ext = 'jpeg';
                $source_gd_image = imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $ext = 'png';
                $source_gd_image = imagecreatefrompng($source_image_path);
                break;
        }

        return empty($source_gd_image) ? null : new \models\misc\Image($source_gd_image, $ext);
    }

    public static function imageToBytes($img, $imgExt) {
        $imgFunc = 'image' . $imgExt;

        ob_start();
        $imgFunc($img);

        return ob_get_clean();
    }

    public static function cropAndResizeImage($x, $y, $w, $h, $targetImage, $resizeW, $resizeH, $imgExt) {
        $croppedImage = self::cropImage($x, $y, $w, $h, $targetImage, $imgExt);

        $resizedImage =  self::resizeImage($resizeW, $resizeH, $croppedImage);
        imagedestroy($croppedImage);

        return $resizedImage;
    }

    public static function cropImage($x, $y, $w, $h, $targetImage, $imgExt) {
        $func = "imagecreatefrom$imgExt";

        $canvas = imagecreatetruecolor($w, $h);

        $current_image = $targetImage;

        list($current_width, $current_height) = getimagesize($targetImage);

        imagecopy(
                $canvas,
                $current_image,
                0, 0,
                $x, $y,
                $current_width, $current_height
        );

        imagedestroy($current_image);

        return $canvas;
    }
    
    public static function cropImage1($x, $y, $w, $h, $targetImage, $imgExt = null) {
        $canvas = imagecreatetruecolor($w, $h);

        $current_width = imagesx($targetImage);
        $current_height = imagesy($targetImage);
        $current_image = $targetImage;

        imagecopy(
                $canvas,
                $current_image,
                0, 0,
                $x, $y,
                $current_width, $current_height
        );

        imagedestroy($current_image);

        return $canvas;
    }

    public static function cropAndResizeImage1($x, $y, $w, $h, $targetImage, $resizeW, $resizeH) {
        $croppedImage = self::cropImage1($x, $y, $w, $h, $targetImage);

        $resizedImage =  self::resizeImage($resizeW, $resizeH, $croppedImage);
        imagedestroy($croppedImage);

        return $resizedImage;
    }
    
    public static function cropAndResizeImage2($x, $y, $w, $h, $targetImage, $resizeW, $resizeH) {
        $resizedImage =  self::resizeImage($resizeW, $resizeH, $targetImage);
        
        $croppedImage = self::cropImage1($x, $y, $w, $h, $resizedImage);
        imagedestroy($resizedImage);

        return $croppedImage;
    }
    
    public static function scaleAndCrop($x, $y, $w, $h, $targetImage, $resizeW, $resizeH) {
        $resizedImage =  self::scaleImage($targetImage, $resizeW, $resizeH);

        $_w = imagesx($resizedImage);

        if ($w > $_w) {
            $w = $h = $_w;
        }

        $_h = imagesy($resizedImage);

        if ($h > $_h) {
            $h = $w = $_h;
        }

        $croppedImage = self::cropImage1($x, $y, $w, $h, $resizedImage);
        imagedestroy($resizedImage);

        return $croppedImage;
    }
    
    /**
     * Resizes an image.
     * @param integer $w resize width
     * @param integer $h resize height
     * @param resource $image the image to be resized
     * @return resource the resized image
     */
    public static function resizeImage($w, $h, $image) {
        $resizedImage = imagecreatetruecolor($w, $h);

        imagecopyresampled(
                $resizedImage, $image, 0, 0, // dest x, y
                0, 0, // src x, y
                $w, $h, //$_w, $_h, // dest w, h
                imagesx($image), imagesy($image) // src w, h
        );

        return $resizedImage;
    }

    public static function scaleImage($source_image_path, $maxWidth, $maxHeight) {
        if (is_string($source_image_path)) {
            list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);

            switch ($source_image_type) {
                case IMAGETYPE_GIF:
                    $source_gd_image = imagecreatefromgif($source_image_path);
                    break;
                case IMAGETYPE_JPEG:
                    $source_gd_image = imagecreatefromjpeg($source_image_path);
                    break;
                case IMAGETYPE_PNG:
                    $source_gd_image = imagecreatefrompng($source_image_path);
                    break;
            }
        } else {
            $source_image_width = imagesx($source_image_path);
            $source_image_height = imagesy($source_image_path);
            $source_gd_image = $source_image_path;
        }

        if ($source_gd_image === false) {
            return false;
        }

        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $maxWidth / $maxHeight;

        if ($source_image_width <= $maxWidth && $source_image_height <= $maxHeight) {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($maxHeight * $source_aspect_ratio);
            $thumbnail_image_height = $maxHeight;
        } else {
            $thumbnail_image_width = $maxWidth;
            $thumbnail_image_height = (int) ($maxWidth / $source_aspect_ratio);
        }

        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled(
                $thumbnail_gd_image, $source_gd_image,
                0, 0, 0, 0,
                $thumbnail_image_width, $thumbnail_image_height,
                $source_image_width, $source_image_height
        );

        return $thumbnail_gd_image;
    }

}
