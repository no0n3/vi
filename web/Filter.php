<?php
namespace web;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
abstract class Filter {

    public function beforeAction($actionId = null) {
        return true;
    }

    public function afterAction() {
    }

}
