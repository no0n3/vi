<?php
namespace models\forms;

class SignUpForm extends \models\BaseModel {

    public $username;
    public $password;

    public function rules() {
        return [
            'email' => [
                'type' => 'email',
            ],
            'username' => [
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
            'password' => [
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
        ];
    }

    public function getAttributeLabels() {
        return [
            'email' => 'Email',
            'username' => 'Username',
            'password' => 'Password'
        ];
    }

}
