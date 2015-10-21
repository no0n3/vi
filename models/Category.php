<?php
namespace models;

use Vi;

class Category {

    public static function getIdByName($categoryName) {
        $stmt = Vi::$app->db->prepare("SELECT `id` FROM `categories` WHERE `name` = :categoryName");

        $stmt->execute([
            ':categoryName' => $categoryName
        ]);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return 1 === count($result) ? $result[0]['id'] : null;
    }

    public static function getAllCategories() {
        return (new \components\db\Query())
            ->select('`id`, `name`')
            ->from('categories')
            ->asAssoc()
            ->all();
    }

}
