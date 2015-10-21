<?php
namespace controllers;

use Vi;
use components\web\Controller;
use components\helpers\ImageHelper;

class CommentController extends Controller {

    private $updateFacade;

    public function rules() {
        return [
            Controller::ALL => [
                'response_type' => 'application\json',
                'roles' => [Controller::REQUIRED_LOGIN],
                'methods' => ['post']
            ],
            'load' => [
                'response_type' => 'application\json',
                'roles' => [Controller::ALL],
                'methods' => ['get']
            ],
            'loadReplies' => [
                'response_type' => 'application\json',
                'roles' => [Controller::ALL],
                'methods' => ['get']
            ],
            'create' => [
                'response_type' => 'application\json',
                'roles' => [Controller::REQUIRED_LOGIN],
            ],
            'upvote' => [
                'response_type' => 'application\json',
                'roles' => [Controller::REQUIRED_LOGIN],
                'methods' => ['post']
            ],
            'unvote' => [
                'response_type' => 'application\json',
                'roles' => [Controller::REQUIRED_LOGIN],
                'methods' => ['post']
            ],
        ];
    }

    public function doCreate() {
        $content = Vi::$app->request->get('content');
        $updateId = (int) Vi::$app->request->get('updateId');

        if (empty($content) || empty($updateId)) {
            return false;
        }

        $replyTo = (int) Vi::$app->request->get('replyTo');

        $result = \models\Comment::create(
            $content,
            $updateId,
            Vi::$app->user->identity->id,
            0 === $replyTo ? null : $replyTo
        );

        if ($result) {
            $result->owner = [
                'id' => Vi::$app->user->identity->id,
                'username' => Vi::$app->user->identity->username,
                'profileUrl' => \models\User::getProfileUrl(Vi::$app->user->identity->id),
                'pictureUrl' => Vi::$app->user->identity->getProfilePicUrl()
            ];
            $result->postedAgo = \models\BaseModel::getPostedAgoTime(date("Y-m-d H:i:s", time()));
            $result->upvotes = 0;
            $result->replies = $replyTo ? false : [];
            $result->voted = false;
        }

        return json_encode($result);    }

    public function doUpvote() {
        Vi::$app->db->beginTransaction();

        $result = \models\Comment::upvote(
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

        $result = \models\Comment::unvote(
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

    public function doLoad() {
        $result = \models\Comment::getComments(
            Vi::$app->request->get('updateId'),
            Vi::$app->request->get('page')
        );

        Vi::$app->db->close();

        if (null === $result) {
            throw new \components\exceptions\BadRequestException();
        }

        return json_encode($result);
    }

    public function doLoadReplies() {
        $result = \models\Comment::getReplies(
            Vi::$app->request->get('replyTo'),
            Vi::$app->request->get('last')
        );

        Vi::$app->db->close();

        if (null === $result) {
            throw new \components\exceptions\BadRequestException();
        }

        return json_encode($result);
    }

}
