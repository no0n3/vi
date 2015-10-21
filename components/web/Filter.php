<?php
namespace components\web;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
abstract class Filter {

    public function beforeAction($actionId) {
        return true;
    }

    public function afterAction() {
    }

}
