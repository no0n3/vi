<?php
namespace models;

use Vi;

class User extends BaseModel {

    const IMAGE_MEDIUM_SIZE = 200;
    const IMAGE_SMALL_SIZE = 50;

    public $lqlq;
    public $password;
    private $categories;

    public function getAttributeLabels() {
        return [
            'lqlq' => 'VAVEDI LQLQ',
            'password' => 'Password'
        ];
    }

    public static function getOne($id) {
        if (!is_numeric($id)) {
            return null;
        }

        $users = (new \components\db\Query())
            ->select('id, username, description, has_profile_pic')
            ->from('users')
            ->where(['id' => (int) $id])
            ->asAssoc()
            ->all();

        return 1 === count($users) ? $users[0] : null;
    }

    public static function getProfileUrl($userId) {
        return '/profile/' . $userId;
    }

    public function getCategories() {
        if (null !== $this->categories) {
            return $this->categories;
        }

        if (null === $this->id) {
            return null;
        }

        $stmt = \Vi::$app->db->executeQuery('SELECT `category_id` FROM `user_categories` WHERE `user_id` = ' . $this->id);
        $result = $stmt->fetchAll(\PDO::FETCH_NUM);

        return \components\helpers\ArrayHelper::column($result, 'category_id');
    }

    public static function findUser($id) {
        $id = (int) $id;

        $stmt = \Vi::$app->db->executeQuery('SELECT `id`, `username`, `description` FROM `users` WHERE `id` = ' . $id);
        $result = $stmt->fetchAll(\PDO::FETCH_CLASS, self::getClassName());

        return 0 < count($result) ? $result[0] : null;
    }

    public function getProfilePicUrl()
    {
        return self::getProfilePictureUrl($this->hasProfilePic, $this->id);
    }

    public static function getProfilePictureUrl($hasProfilePic, $userId) {
        return (bool) !$hasProfilePic ? '/images/default_avatar.jpg' : ("/images/users/$userId/200x200.jpeg");
    }
}
