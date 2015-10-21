<?php

namespace console\controllers;

use console\logic\Migration;

class MigrateController {

    public function actionIndex() {
        $this->actionUp();
    }

    public function actionCreate($params) {
        Migration::create($params);
    }
 
    public function actionUp() {
        Migration::up();
    }

}
