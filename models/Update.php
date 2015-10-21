<?php
namespace models;

use Vi;

class Update {

    const TYPE_FRESH = 'fresh';
    const TYPE_TRENDING = 'trending';

    const IMAGE_BIG_WIDTH = 800;
    const IMAGE_MEDIUM_WIDTH = 500;
    const IMAGE_SMALL_WIDTH = 250;

    const ACTIVITY_TYPE_UPVOTE = 1;
    const ACTIVITY_TYPE_COMMENT = 2;
    const ACTIVITY_TYPE_POST = 4;

    const POPULAR_UPDATES_LIMIT = 15;
    const UPDATES_LOAD_COUNT = 10;

    const TRENDING_MIN_RATE = 100;
    const MOST_POPULAR_MIN_RATE = 100;

    public static function isValidType($type) {
        return in_array($type, [self::TYPE_FRESH, self::TYPE_TRENDING]);
    }

    public static function getUpdates($page, $categoryId = null, $type = self::TYPE_FRESH) {
        $page = ($page ? ($page * self::UPDATES_LOAD_COUNT + self::UPDATES_LOAD_COUNT) : 0);

        if (self::isValidType($type)) {
            $where = sprintf(" AND `rate` > %d ");
        }

        if (empty($categoryId)) {
            if ($type === self::TYPE_FRESH) {
                $query = "SELECT *, "
                    . (Vi::$app->user->isLogged() ? ("0 < (SELECT count(*) FROM `update_upvoters` WHERE `update_id` = `updates`.`id` AND `user_id` = " . Vi::$app->user->identity->id . ") ") : ' false ')
                    . " `voted` FROM `updates` ORDER BY `created_at` DESC LIMIT " . self::UPDATES_LOAD_COUNT . " OFFSET $page";
            } else {
                $query = "SELECT *, "
                    . (Vi::$app->user->isLogged() ? ("0 < (SELECT count(*) FROM `update_upvoters` WHERE `update_id` = `updates`.`id` AND `user_id` = " . Vi::$app->user->identity->id . ") ") : ' false ')
                    . " `voted` FROM `updates` "
                    . ((self::isValidType($type) && $type == self::TYPE_TRENDING) ? (' WHERE `upvotes` > ' . self::TRENDING_MIN_RATE) : '')
                    . " ORDER BY `rate` ASC LIMIT " . self::UPDATES_LOAD_COUNT . " OFFSET $page";
            }
        } else {
            if (!is_numeric($categoryId)) {
                return [];
            }

            if ($type === self::TYPE_FRESH) {
                $query = "SELECT u.*, "
                    . (Vi::$app->user->isLogged() ? ("0 < (SELECT count(*) FROM `update_upvoters` WHERE `update_id` = `u`.`id` AND `user_id` = " . Vi::$app->user->identity->id . ") ") : ' false ')
                    . " `voted` FROM `updates` u JOIN `update_categories` uc ON uc.`update_id` = u.id WHERE uc.category_id = $categoryId "
                    . " ORDER BY `created_at` DESC LIMIT " . self::UPDATES_LOAD_COUNT . " OFFSET $page";
            } else {
                $query = "SELECT u.*, "
                    . (Vi::$app->user->isLogged() ? ("0 < (SELECT count(*) FROM `update_upvoters` WHERE `update_id` = `u`.`id` AND `user_id` = " . Vi::$app->user->identity->id . ") ") : ' false ')
                    . " `voted` FROM `updates` u JOIN `update_categories` uc ON uc.`update_id` = u.id WHERE uc.category_id = $categoryId "
                    . ((self::isValidType($type) && $type == self::TYPE_TRENDING) ? (' AND `rate` > ' . self::TRENDING_MIN_RATE) : '')
                    . " ORDER BY `rate` ASC LIMIT " . self::UPDATES_LOAD_COUNT . " OFFSET $page";
            }
        }

        $stmt = Vi::$app->db->executeQuery($query);
        $updates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $updatesCount = count($updates);

        for ($i = 0; $i < $updatesCount; $i++) {
            $updates[$i]['description'] = htmlspecialchars($updates[$i]['description']);
            $updates[$i]['imgUrl'] = '/images/updates/' . $updates[$i]['id'] . '/' . self::IMAGE_MEDIUM_WIDTH . 'xX.jpeg';
            $updates[$i]['updateUrl'] = self::getUpdateUrl($updates[$i]['id']);
            $updates[$i]['postedAgo'] = BaseModel::getPostedAgoTime($updates[$i]['created_at']);
            $updates[$i]['created_at'] = strtotime($updates[$i]['created_at']);
            $updates[$i]['voted'] = (bool) $updates[$i]['voted'];
        }

        return $updates;
    }

    public static function getUserUpdates($time, $userId) {
        if (!is_numeric($userId)) {
            return false;
        }

        $time = $time ? (" WHERE ua.`time` < '" . date("Y-m-d H:i:s", $time) . "'") : '';
        $stmt = Vi::$app->db->executeQuery("SELECT u.*, ua.activity_type, "
                . (Vi::$app->user->isLogged() ? ("0 < (SELECT count(*) FROM `update_upvoters` WHERE `update_id` = `u`.`id` AND `user_id` = " . Vi::$app->user->identity->id . ") ") : ' false ')
                . " `voted` FROM `updates` u JOIN `user_update_activity` ua ON ua.update_id = u.id WHERE ua.`user_id` = $userId $time ORDER BY ua.`time` DESC LIMIT " . self::UPDATES_LOAD_COUNT);

        $updates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $updatesCount = count($updates);

        for ($i = 0; $i < $updatesCount; $i++) {
            $updates[$i]['description'] = htmlspecialchars($updates[$i]['description']);
            $updates[$i]['imgUrl'] = '/images/updates/' . $updates[$i]['id'] . '/' . self::IMAGE_MEDIUM_WIDTH . 'xX.jpeg';
            $updates[$i]['updateUrl'] = self::getUpdateUrl($updates[$i]['id']);
            $updates[$i]['postedAgo'] = BaseModel::getPostedAgoTime($updates[$i]['created_at']);
            $updates[$i]['created_at'] = strtotime($updates[$i]['created_at']);
            $updates[$i]['voted'] = (bool) $updates[$i]['voted'];
        }

        return $updates;
    }

    public static function getPopularUpdates($categoryId = null) {
        $query = "SELECT * FROM `updates` WHERE `created_at` > '" . (date("Y-m-d H:i:s", strtotime('-3 days'))) . "' AND `rate` > " . self::MOST_POPULAR_MIN_RATE . " ORDER BY `rate` DESC LIMIT " . self::POPULAR_UPDATES_LIMIT;

        $stmt = Vi::$app->db->executeQuery($query);

        $updates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $updatesCount = count($updates);

        for ($i = 0; $i < $updatesCount; $i++) {
            $updates[$i]['description'] = htmlspecialchars($updates[$i]['description']);
            $updates[$i]['imgUrl'] = '/images/updates/' . $updates[$i]['id'] . '/' . self::IMAGE_MEDIUM_WIDTH . 'xX.jpeg';
            $updates[$i]['updateUrl'] = self::getUpdateUrl($updates[$i]['id']);
            $updates[$i]['postedAgo'] = BaseModel::getPostedAgoTime($updates[$i]['created_at']);
            $updates[$i]['created_at'] = strtotime($updates[$i]['created_at']);
        }

        return $updates;
    }

    public static function getOne($id) {
        if (!is_numeric($id)) {
            return null;
        }

        $stmt = Vi::$app->db->executeQuery("SELECT *, "
            . (Vi::$app->user->isLogged() ? ("0 < (SELECT count(*) FROM `update_upvoters` WHERE `update_id` = `updates`.`id` AND `user_id` = " . Vi::$app->user->identity->id . ") ") : ' false ')
            . " `voted` FROM `updates` WHERE `id` = $id");
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (1 === count($result)) {
            $result[0]['description'] = htmlspecialchars($result[0]['description']);
            $result[0]['postedAgo'] = BaseModel::getPostedAgoTime($result[0]['created_at']);
            $result[0]['voted'] = (bool) $result[0]['voted'];

            return $result[0];
        }

        return null;
    }

    public static function getNext($id, $category) {
        if (!is_numeric($id)) {
            return null;
        }

        if (!empty($category)) {
            $stmt = Vi::$app->db->prepare("SELECT u.`created_at`, u.`upvotes`, u.upvotes, uc.category_id FROM `updates` u JOIN `update_categories` uc ON uc.update_id = u.id JOIN categories c ON c.id = uc.category_id WHERE u.`id` = :id AND c.name = :categoryName");
            $stmt->execute([
                ':id' => $id,
                ':categoryName' => $category
            ]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                $createdAt = $result[0]['created_at'];
                $upvotes = $result[0]['upvotes'];
                $rate = $result[0]['rate'];
                $categoryId = $result[0]['category_id'];
            } else {
                return null;
            }
        } else {
            $stmt = Vi::$app->db->executeQuery("SELECT `created_at`, `rate`, `upvotes` FROM `updates` WHERE `id` = $id");
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                $createdAt = $result[0]['created_at'];
                $rate = $result[0]['rate'];
                $upvotes = $result[0]['upvotes'];
            } else {
                return null;
            }
        }

        if (isset($categoryId)) {
            $query = "SELECT u.`id` FROM `updates` u JOIN update_categories uc ON uc.update_id = u.id WHERE uc.category_id = $categoryId AND "
                . (self::isTrending($upvotes) ? "u.`rate` < $rate" : "u.`created_at` < '$createdAt'")
                . " ORDER BY "
                . (self::isTrending($upvotes) ? "u.`rate`" : "u.`created_at`")
                . " DESC LIMIT 1";

            $stmt = Vi::$app->db->executeQuery($query);

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                return $result[0]['id'];
            }

            return null;
        } else {
            $query = "SELECT `id` FROM `updates` WHERE "
                . (self::isTrending($upvotes) ? "`rate` < $rate" : "`created_at` < '$createdAt'")
                . " ORDER BY "
                . (self::isTrending($upvotes) ? "`rate`" : "`created_at`")
                . " DESC LIMIT 1";

            $stmt = Vi::$app->db->executeQuery($query);

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                return $result[0]['id'];
            }

            return null;
        }
    }

    public static function getPrev($id, $category = null) {
        if (!is_numeric($id)) {
            return null;
        }

        if (!empty($category)) {
            $stmt = Vi::$app->db->prepare("SELECT u.`created_at`, u.`upvotes`, u.upvotes, uc.category_id FROM `updates` u JOIN `update_categories` uc ON uc.update_id = u.id JOIN categories c ON c.id = uc.category_id WHERE u.`id` = :id AND c.name = :categoryName");
            $stmt->execute([
                ':id' => $id,
                ':categoryName' => $category
            ]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                $createdAt = $result[0]['created_at'];
                $upvotes = $result[0]['upvotes'];
                $rate = $result[0]['rate'];
                $categoryId = $result[0]['category_id'];
            } else {
                return null;
            }
        } else {
            $stmt = Vi::$app->db->executeQuery("SELECT `created_at`, `rate`, `upvotes` FROM `updates` WHERE `id` = $id");
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                $createdAt = $result[0]['created_at'];
                $rate = $result[0]['rate'];
                $upvotes = $result[0]['upvotes'];
            } else {
                return null;
            }
        }

        if (isset($categoryId)) {
            $query = "SELECT u.`id` FROM `updates` u JOIN update_categories uc ON uc.update_id = u.id WHERE uc.category_id = $categoryId AND "
                . (self::isTrending($upvotes) ? "u.`rate` > $rate" : "u.`created_at` > '$createdAt'")
                . " ORDER BY "
                . (self::isTrending($upvotes) ? "u.`rate`" : "u.`created_at`")
                . " ASC LIMIT 1";

            $stmt = Vi::$app->db->executeQuery($query);

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                return $result[0]['id'];
            }

            return null;
        } else {
            $query = "SELECT `id` FROM `updates` WHERE "
                . (self::isTrending($upvotes) ? "`rate` > $rate" : "`created_at` > '$createdAt'")
                . " ORDER BY "
                . (self::isTrending($upvotes) ? "`rate`" : "`created_at`")
                . " ASC LIMIT 1";

            $stmt = Vi::$app->db->executeQuery($query);

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (1 === count($result)) {
                return $result[0]['id'];
            }

            return null;
        }
    }

    private static function isTrending($upvotes) {
        return $upvotes > self::TRENDING_MIN_RATE;
    }

    public static function getUpdateImageUrl($updateId, $size = self::IMAGE_MEDIUM_WIDTH) {
        return '/images/updates/' . $updateId . '/' . $size . 'xX.jpeg';
    }

    public static function getUpdateUrl($updateId) {
        return '/update/' . $updateId;
    }

    public static function upvote($updateId, $userId) {
        if (!is_numeric($updateId) || !is_numeric($userId)) {
            return false;
        }

        if (!self::addActivity($updateId, $userId, self::ACTIVITY_TYPE_UPVOTE)) {
            return false;
        }
        $stmt = Vi::$app->db->executeQuery("SELECT `upvotes`, `created_at` FROM `updates` WHERE `id` = $updateId");
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (0 < count($result)) {
            $rate = self::calculateRankSum($result[0]['upvotes'] + 1, strtotime($result[0]['created_at']));
        }
        Vi::$app->db->executeUpdate("INSERT INTO `update_upvoters` (`user_id`, `update_id`) VALUES ($userId, $updateId)");

        if (0 >= Vi::$app->db->executeUpdate("UPDATE `updates` SET upvotes = upvotes + 1, rate = $rate WHERE `id` = $updateId")) {
            return false;
        }

        return true;
    }

    public static function unvote($updateId, $userId) {
        if (!is_numeric($updateId) || !is_numeric($userId)) {
            return false;
        }

        if (!self::removeActivity($updateId, $userId, self::ACTIVITY_TYPE_UPVOTE)) {
            return false;
        }

        if (0 >= Vi::$app->db->executeUpdate("DELETE FROM `update_upvoters` WHERE `user_id` = $userId AND `update_id` = $updateId")) {
            return false;
        }

        $stmt = Vi::$app->db->executeQuery("SELECT `upvotes`, `created_at` FROM `updates` WHERE `id` = $updateId");
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
 
        if (0 < count($result)) {
            $rate = self::calculateRankSum($result[0]['upvotes'] - 1, strtotime($result[0]['created_at']));
        }

        if (0 >= Vi::$app->db->executeUpdate("UPDATE `updates` SET upvotes = upvotes - 1, $rate WHERE `id` = $updateId")) {
            return false;
        }

        return true;
    }

    public static function addActivity($updateId, $userId, $activityType) {
        if (0 >= Vi::$app->db->executeUpdate(
           "INSERT INTO `user_update_activity` (`user_id`, `update_id`, `activity_type`) VALUES ($userId, $updateId, $activityType) "
           . "ON DUPLICATE KEY UPDATE `user_id` = $userId, `update_id` = $updateId, `activity_type` = `activity_type` | $activityType, `time` = now()"
        )) {
            return false;
        }

        return true;
    }

    public static function removeActivity($updateId, $userId, $activityType) {
        if (0 >= Vi::$app->db->executeUpdate(
           "DELETE FROM `user_update_activity` WHERE `user_id` = $userId AND `update_id` = $updateId AND `activity_type` = $activityType"
        )) {
            if (0 >= Vi::$app->db->executeUpdate(
                "UPDATE `user_update_activity` SET `activity_type` = `activity_type` ^ $activityType WHERE `user_id` = $userId AND `update_id` = $updateId"
            )) {
                return false;
            }
        }

        return true;
    }

    public static function getUpdateCategories($updateId) {
        if (!is_numeric($updateId) || 0 >= $updateId) {
            return [];
        }

        $stmt = Vi::$app->db->executeQuery("SELECT c.`name` FROM `categories` c JOIN `update_categories` uc ON c.`id` = uc.`category_id` WHERE uc.`update_id` = $updateId");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    static function calculateRankSum($score, $created_at) {
//        $s = ($score) / pow(($created_at+2), 1.8);
//        return 0 >= $s ? $created_at : $s;
        $order = log10(max(abs($score), 1));

        if ( $score > 0 ) {
           $sign = 1;
        } elseif ( $score < 0 ) {
           $sign = -1; 
        } else {
           $sign = 0;
        }

        $seconds = intval(($created_at - mktime(0, 0, 0, 1, 1, 1970)) / 8640);

	$long_number = (($order + $sign) == 0 ? 1 :($order + $sign)) * ($seconds);

	return round($long_number, 7);
   } 
}
