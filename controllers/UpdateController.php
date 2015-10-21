<?php
namespace controllers;

use Vi;
use models\Update;
use components\web\Controller;
use components\helpers\ImageHelper;
use models\Category;

class UpdateController extends BaseController {

    public function rules() {
        return [
            Controller::ALL => [
                'response_type' => 'application\json',
                'roles' => [Controller::REQUIRED_LOGIN],
                'methods' => ['post']
            ],
            'load' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL],
                'methods' => ['get']
            ],
            'create' => [
                'response_type' => 'text\html',
                'roles' => [Controller::REQUIRED_LOGIN],
            ],
            'upload' => [
                'response_type' => 'text\html',
            ],
            'view' => [
                'response_type' => 'text\html',
                'roles' => [Controller::ALL],
                'methods' => ['get']
            ],
            'load' => [
                'response_type' => 'application\json',
                'roles' => [Controller::ALL],
                'methods' => ['get']
            ],
            'userUpdates' => [
                'response_type' => 'application\json',
                'roles' => [Controller::ALL],
                'methods' => ['get']
            ],
//            'next' => [
//                'response_type' => 'application\json',
//                'roles' => [Controller::ALL],
//                'methods' => ['get']
//            ],
//            'prev' => [
//                'response_type' => 'application\json',
//                'roles' => [Controller::ALL],
//                'methods' => ['get']
//            ],
        ];
    }

    public function doCreate() {
        $form = new \models\forms\UpdateForm();

        if ($form->load(\Vi::$app->request->post()) &&
            $form->validate()
        ) {
            Vi::$app->db->beginTransaction();

            if ($form->save()) {
                Vi::$app->db->commit();
            } else {
                Vi::$app->db->rollback();
            }

            Vi::$app->db->close();

            $success = true;
        } else {
            $success = false;
        }

        return $this->render('create', [
            'success' => $success,
            'model' => $form,
            'categories' => \models\Category::getAllCategories()
        ]);
    }

    public function doView() {
        $updateId = Vi::$app->request->get('id');
        $type = Vi::$app->request->get('type');
        $categoryName = Vi::$app->request->get('category');

        $update = \models\Update::getOne(
            $updateId,
            $type,
            $categoryName
        );

        if (null !== $update) {
            $update['imageUrl'] = Update::getUpdateImageUrl($update['id'], Update::IMAGE_BIG_WIDTH);
            $categories = Update::getUpdateCategories($update['id']);
        } else {
            $categories = [];
        }

        Vi::$app->db->close();

        return $this->render('view', [
            'update' => $update,
            'categories' => $categories,
            'categoryName' => $categoryName,
            'prevUpdateId' => null === $update ? null : Update::getPrev($updateId, $categoryName),
            'nextUpdateId' => null === $update ? null : Update::getNext($updateId, $categoryName)
        ]);
    }

    public function doUpvote() {
        Vi::$app->db->beginTransaction();

        $result = \models\Update::upvote(
            Vi::$app->request->post('id'),
            Vi::$app->user->identity->id
        );

        if ($result) {
            Vi::$app->db->commit();
        } else {
            Vi::$app->db->rollback();
        }

        Vi::$app->db->close();

        return json_encode($result);
    }

    public function doUnvote() {
        Vi::$app->db->beginTransaction();

        $result = \models\Update::unvote(
            Vi::$app->request->post('id'),
            Vi::$app->user->identity->id
        );

        if ($result) {
            Vi::$app->db->commit();
        } else {
            Vi::$app->db->rollback();
        }

        Vi::$app->db->close();

        return json_encode($result);
    }

    public function doUserUpdates() {
        $result = Update::getUserUpdates(
            Vi::$app->request->get('last'),
            Vi::$app->request->get('userId')
        );

        Vi::$app->db->close();

        return json_encode($result);
    }

    public function doLoad() {
        $result = Update::getUpdates(
            Vi::$app->request->get('page'),
            Category::getIdByName(Vi::$app->request->get('category')),
            Vi::$app->request->get('type')
        );

        Vi::$app->db->close();

        return json_encode($result);
    }

//    public function doPrev() {
//        $updateId = Vi::$app->request->get('id');
//        $categoryName = Vi::$app->request->get('category');
//
//        $prevUpdateId = Update::getPrev($updateId, $categoryName);
//
//        Vi::$app->db->close();
//
//        return json_encode($prevUpdateId);
//    }
//
//    public function doNext() {
//        $updateId = Vi::$app->request->get('id');
//        $categoryName = Vi::$app->request->get('category');
//
//        $nextUpdateId = Update::getNext($updateId, $categoryName);
//
//        Vi::$app->db->close();
//
//        return json_encode($nextUpdateId);
//    }

}
