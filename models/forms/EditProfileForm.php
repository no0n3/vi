<?php
namespace models\forms;

use Vi;
use components\helpers\ArrayHelper;

class EditProfileForm extends \models\BaseModel {

    public $username;
    public $description;

    public function rules() {
        return [
            'username' => [
                'filter' => function($value) {
                    return $value;
                },
                'validator' => function($name, $value) {
                    return $value < 2 || $value > 255;
                },
                'clientValidator' => function() {
                    return <<<JS
                        function (value) {
                            return value.length < 2 || value.length > 255;
                        }
JS;
                },
                'message' => 'Username must be at least 2 characters long.'
            ],
            'description' => [
                'filter' => function($value) {
                    return $value;
                },
                'validator' => function($name, $value) {
                    return $value < 6 || $value > 255;
                },
                'clientValidator' => function() {
                    return <<<JS
                        function (value) {
                            return value.length < 6 || value.length > 255;
                        }
JS;
                },
                'message' => 'Password must be at least 6 characters long.'
            ],
            'categories' => []
        ];
    }

    public function getAttributeLabels() {
        return [
            'username' => 'Username',
            'description' => 'Description'
        ];
    }

    public function save() {
        $toAdd = [];
        $toDelete = [];

        Vi::$app->db->beginTransaction();

        foreach ($this->categories as $category) {
            if (!in_array($category, $this->userCategories)) {
                $toAdd[] = $category;
            }
        }

        foreach ($this->userCategories as $category) {
            if (!in_array($category, $this->categories)) {
                $toDelete[] = $category;
            }
        }

        if (0 < count($toAdd)) {
            $query = sprintf(
                "INSERT INTO `user_categories` (`user_id`, `category_id`) VALUES %s",
                ArrayHelper::getArrayToString($toAdd, ',', function ($v) {
                    return "($this->userId, $v)";
                })
            );
            Vi::$app->db->executeUpdate($query);
        }

        if (0 < count($toDelete)) {
            $query = sprintf(
                "DELETE FROM `user_categories` WHERE `user_id` = %d AND `category_id` IN (%s)",
                $this->userId,
                ArrayHelper::getArrayToString($toDelete, ',')
            );
            Vi::$app->db->executeUpdate($query);
        }

        $stmt = Vi::$app->db->prepare('UPDATE `users` SET `username` = :username, `description` = :description WHERE `id` = :userId');
        $success = $stmt->execute([
            ':username' => $this->username,
            ':description' => $this->description,
            ':userId' => $this->userId
        ]);

        Vi::$app->db->commit();

        return $success;
    }

}
