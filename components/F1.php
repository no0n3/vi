<?php
namespace components;

class F1 extends \components\web\Filter {
    public function beforeAction($actionId) {
        return true;
    }
}
