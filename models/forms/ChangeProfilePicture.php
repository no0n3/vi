<?php
namespace models\forms;

use Vi;
use models\Update;
use models\User;
use components\helpers\ImageHelper;

class ChangeProfilePicture extends \models\BaseModel {

    public $image;

    public function rules() {
        return [
            'image' => [
                'type' => self::TYPE_IMAGE
            ]
        ];
    }

    public function save() {
       
        $imageDg = $this->image->getImage();
        $ext = "jpeg";//$this->image->getExt();

        Vi::$app->db->beginTransaction();

            $userDir = Vi::$app->params['sitePath'] . 'public_html/images/users/' . $this->userId;

            mkdir($userDir);

            $imageMedium = ImageHelper::scaleAndCrop(
                0, 0,
                User::IMAGE_MEDIUM_SIZE, User::IMAGE_MEDIUM_SIZE,
                $imageDg,
                User::IMAGE_MEDIUM_SIZE, User::IMAGE_MEDIUM_SIZE
            );

            $imageSmall = ImageHelper::scaleAndCrop(
                0, 0,
                User::IMAGE_SMALL_SIZE, User::IMAGE_SMALL_SIZE,
                $imageDg,
                User::IMAGE_SMALL_SIZE, User::IMAGE_SMALL_SIZE
            );

            $func = "imagejpeg";//"image$ext";
            $func($imageMedium, sprintf("%s/%dx%d.%s", $userDir, User::IMAGE_MEDIUM_SIZE, User::IMAGE_MEDIUM_SIZE, $ext));
            $func($imageSmall, sprintf("%s/%dx%d.%s", $userDir, User::IMAGE_SMALL_SIZE, User::IMAGE_SMALL_SIZE, $ext));
            ob_clean();
            header('Content-Type: image\jpeg');

            imagedestroy($imageMedium);
            imagedestroy($imageSmall);

            Vi::$app->db->commit();

        return true;
    }

}
