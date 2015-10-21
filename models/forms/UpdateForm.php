<?php
namespace models\forms;

use Vi;
use models\Update;
use components\helpers\ImageHelper;

class UpdateForm extends \models\BaseModel {

    public $title;
    public $image;
    public $categories;

    public $newUpdateId;

    public function rules() {
        return [
            'title' => [
                'validator' => function($name, $value) {
                    return $value <= 0 || $value > 255;
                },
                'message' => 'Username must be at least 2 characters long.'
            ],
            'categories' => [
                'validator' => function($name, $value) {
                    return 0 < count($value);
                }
            ],
            'image' => [
                'type' => self::TYPE_IMAGE
            ]
        ];
    }

    public function save() {
        $imageDg = $this->image->getImage();
        $ext = "jpeg";

        $stmt = Vi::$app->db->prepare("INSERT INTO `updates` (`user_id`, `description`) VALUES (:userId, :description)");

        if (0 >= $stmt->execute([
            ':userId' => \Vi::$app->user->identity->id,
            ':description' =>  $this->title
        ])) {
            return false;
        }

        $this->newUpdateId = Vi::$app->db->getLastInsertedId();

        if (!Update::addActivity($this->newUpdateId, Vi::$app->user->identity->id, Update::ACTIVITY_TYPE_POST)) {
            return false;
        }

        $a = [];

        foreach ($this->categories as $category) {
            $c = (int) $category;
            $a[] = "({$this->newUpdateId}, $c)";
        }

        $categoryInsert = 'INSERT INTO `update_categories` (`update_id`, `category_id`) VALUES ' . implode(',', $a);

        if (0 >= Vi::$app->db->executeUpdate($categoryInsert)) {
            return false;
        }

        $updateDir = Vi::$app->params['sitePath'] . 'public_html/images/updates/' . $this->newUpdateId;

        mkdir($updateDir);

        $imageBig = ImageHelper::scaleImage($imageDg, Update::IMAGE_BIG_WIDTH, PHP_INT_MAX);
        $imageMedium = ImageHelper::scaleImage($imageDg, Update::IMAGE_MEDIUM_WIDTH, PHP_INT_MAX);
        $imageSmall = ImageHelper::scaleImage($imageDg, Update::IMAGE_SMALL_WIDTH, PHP_INT_MAX);

        $func = "imagejpeg";
        $func($imageBig, sprintf("%s/%dxX.%s", $updateDir, Update::IMAGE_BIG_WIDTH, $ext));
        $func($imageMedium, sprintf("%s/%dxX.%s", $updateDir, Update::IMAGE_MEDIUM_WIDTH, $ext));
        $func($imageSmall, sprintf("%s/%dxX.%s", $updateDir, Update::IMAGE_SMALL_WIDTH, $ext));

        imagedestroy($imageBig);
        imagedestroy($imageMedium);
        imagedestroy($imageSmall);

        return true;
    }

}
