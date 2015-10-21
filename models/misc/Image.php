<?php
namespace models\misc;

class Image {

    private $image;
    private $ext;

    public function __construct($image, $ext) {
        $this->image = $image;
        $this->ext = $ext;
    }

    public function __destruct() {
        imagedestroy($this->image);
    }

    public function getImage() {
        return $this->image;
    }

    public function getExt() {
        return $this->ext;
    }

    public function printImage() {
        $funcName = "image{$this->ext}";
        $funcName($this->image);
    }

    public function saveImage($path) {
        $funcName = "image{$this->ext}";
        $funcName($this->image, $path);
    }

}
