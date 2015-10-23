<?php
namespace web;

abstract class Asset {

    public static function register() {
        AssetLoader::registerAsset(new static());
    }

    function getClassName() {
        return get_called_class();
    }

    public function depends() {
        return [];
    }

    public function assets() {
        return [
            'js' => [],
            'css' => []
        ];
    }

}
