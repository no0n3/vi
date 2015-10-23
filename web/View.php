<?php
namespace web;

use Vi;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class View extends \base\Object {
    protected $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function render($view, $vars = []) {
        ob_start();
        ob_implicit_flush(false);
        extract($vars, EXTR_OVERWRITE);

        include Vi::$app->params['sitePath'] . "/views/{$this->controller->id}/$view.php";

        return ob_get_clean();
    }

    public function renderFile($path, $vars = []) {
        ob_start();
        ob_implicit_flush(false);
        extract($vars, EXTR_OVERWRITE);

        include $path;

        return ob_get_clean();
    }

}
