<?php
namespace controllers;

use Vi;
use components\web\Controller;
use components\helpers\ImageHelper;

class BaseController extends Controller {

    private function actionsToExclude() {
        return [
            
        ];
    }

    public function beforeAction($actionId) {
        if (Vi::$app->request->isPost() && Vi::$app->user->isLogged()) {
            $this->hasCsrfValidation = true;
        }

        if (!in_array("$this->id/$actionId", $this->actionsToExclude())) {
            $stmt = Vi::$app->db->executeQuery('SELECT `name` FROM `categories` ORDER BY `position`');

            $this->view->categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $this->view->categories = [];
        }

        return true;
    }

}
