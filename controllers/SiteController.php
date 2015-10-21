<?php
namespace controllers;

use Vi;
use models\Update;
use components\web\Controller;
use components\helpers\ArrayHelper;

class SiteController extends BaseController {

    public $hasCsrfValidation = false;

    public function rules() {
        return [
            Controller::ALL => [
                'response_type' => 'application\json',
                'roles' => [Controller::REQUIRED_LOGIN],
                'methods' => ['post']
            ],
            'index' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL],
            ],
            'login' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL]
            ],
            'logout' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL],
                'methods' => ['post']
            ],
            'signUp' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL]
            ]
        ];
    }

    public function doIndex() {
        $updates = [];
        $category = Vi::$app->request->get('category');

        if (null !== $category &&
            !in_array($category, ArrayHelper::getKeyArray($this->view->categories, 'name'))
        ) {
            throw new \components\exceptions\NotFoundException();
        }

        $mostPopular = Update::getPopularUpdates(
            \models\Category::getIdByName(
                $category
            )
        );

        return $this->render('index', [
            'updates' => $updates,
            'mostPopular' => $mostPopular,
            'category' => $category
        ]);
    }

    public function doLogin() {
        if (Vi::$app->user->isLogged()) {
            return $this->redirect('index');
        }

        $user = new \models\forms\LoginForm();

        if ($user->load(Vi::$app->request->post()) && $user->validate()) {
            if (Vi::$app->user->login(
                $user->email,
                $user->password
            )) {
                return $this->redirect('index');
            }
        }

        return $this->render('login', ['user' => $user]);
    }

    public function doSignUp() {
        $form = new \models\forms\SignUpForm();

        if ($form->load(Vi::$app->request->post()) && $form->validate()) {
            if (Vi::$app->user->signUp(
                $form->username,
                $form->email,
                $form->password
            )) {
                $success = true;
            } else {
                $success = false;
            }
        }

        return $this->render('signUp', [
            'success' => $success,
            'model' => $form
        ]);
    }

    public function doLogout() {
        if (!Vi::$app->user->isLogged() ||
            Vi::$app->user->logout()
        ) {
            return $this->redirect('login');
        }

        return $this->redirect('login');
    }

}
