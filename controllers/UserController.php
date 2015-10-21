<?php
namespace controllers;

use Vi;
use models\Update;
use components\web\Controller;

class UserController extends BaseController {

    public function rules() {
        return [
            Controller::ALL => [
                'response_type' => 'application\json',
//                'roles' => [Controller::REQUIRED_LOGIN],
//                'methods' => ['post']
            ],
            'load' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL],
                'methods' => ['get']
            ],
            'view' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL],
                'methods' => ['get'],
                'filters' => ['\components\F1']
            ],
            'settings' => [
                'response_type' => 'text\html',
                'roles' => [Controller::REQUIRED_LOGIN],
            ],
            'changePassword' => [
                'response_type' => 'text\html',
                'roles' => [Controller::REQUIRED_LOGIN],
            ],
            'changePicture' => [
                'response_type' => 'text\html',
                'roles' => [Controller::REQUIRED_LOGIN],
            ]
        ];
    }

    public function doView() {
        Vi::$app->user->login(1,1);
//        trigger_error(1);
//        throw new \components\exceptions\NotFoundException;
//        var_dump(Vi::$app->x1);
//        exit;
        $user = \models\User::getOne(
            Vi::$app->request->get('id')
        );

        return $this->render('view', [
            'model' => $user,
            'mostPopular' => Update::getPopularUpdates()
        ]);
    }

    public function doSettings() {
        $user = \models\User::findUser(Vi::$app->user->identity->id);

        $form = new \models\forms\EditProfileForm();
        $form->userCategories = $user->categories;
        $form->userId = $user->id;

        if (empty(Vi::$app->request->post())) {
            $form->username = $user->username;
            $form->description = $user->description;
        } else if ($form->load(Vi::$app->request->post()) &&
                   $form->save()
        ) {
            $_SESSION['user']->username = $form->username;
            $success = true;
        }

        $categories = \models\Category::getAllCategories();

        Vi::$app->db->close();

        $settingType = \Vi::$app->request->param('t');

        return $this->render('edit', [
            'model' => $form,
            'categories' => $categories,
            'settingType' => !in_array($settingType, ['profile', 'password', 'picture']) ? 'profile' : $settingType,
            'success' => isset($success) ? $success : false
        ]);
    }

    public function doChangePicture() {
        $model = new \models\forms\ChangeProfilePicture();

        $model->userId = \Vi::$app->user->identity->id;

        if ($model->load(Vi::$app->request->post()) &&
            $model->save()
        ) {
            $success = true;
        }

        return $this->render('edit', [
            'id' => Vi::$app->request->get('id'),
            'model' => $model,
            'settingType' => 'picture',
            'success' => isset($success) ? $success : false
        ]);
    }

    public function doChangePassword() {
        $model = new \models\forms\ChangePasswordForm();

        $model->userId = \Vi::$app->user->identity->id;

        if ($model->load(Vi::$app->request->post()) &&
            $model->save()
        ) {
            $success = true;
        }

        return $this->render('edit', [
            'id' => Vi::$app->request->get('id'),
            'model' => $model,
            'settingType' => 'password',
            'success' => isset($success) ? $success : false
        ]);
    }

}
