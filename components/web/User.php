<?php
namespace components\web;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class User {

    const LOGGED_USER = 'user';
    public $identity;
    public $identityName = self::LOGGED_USER;

    public function __construct() {
        $this->identity = $_SESSION[$this->identityName];
    }

    public function isLogged() {
        return !empty($_SESSION[$this->identityName]);
    }

    public function inRole($roles) {
        if (in_array(Controller::REQUIRED_LOGIN, $roles)) {
            return $this->isLogged();
        } else if (in_array('not_logged', $roles)) {
            return !$this->isLogged();
        }

        return true;
    }

    public function login($email, $password) {
        echo $this->identityClass;
        exit;
        if ($this->isLogged()) {
            return true;
        }

        $result = (new \components\db\Query())->select("id, username, email, password, has_profile_pic")
                ->from('users')
                ->where([
                   'email' => $email
                ])
                ->asAssoc()
                ->all();

        if (!empty($result)) {
            $result = $result[0];

            $loginSuccess = \components\Security::verifyHash($password, $result['password']);

            if ($loginSuccess) {
                $logedUser = new \models\User();
                $logedUser->id = $result['id'];
                $logedUser->username = $result['username'];
                $logedUser->hasProfilePic = $result['has_profile_pic'];

                $this->identity = $_SESSION['user'] = $logedUser;
                $_SESSION['_csrf'] = sprintf("%d-%s", $logedUser->id, uniqid());
            }
        }

        return $loginSuccess;
    }

    public function signUp($username, $email, $password) {
        return (new \components\db\Query())
            ->insert('users',
                [
                    'username' => ':username',
                    'password' => ':password',
                    'email' => ':email'
                ],
                [
                    ':username' => $username,
                    ':password' => \components\Security::hash($password),
                    ':email' => $email
                ]
            )
            ->execute();
    }

    public function logout() {
        if ($this->isLogged()) {
            
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if (isset($_SESSION[$this->identityName])) {
                $loggedUserId = $_SESSION[$this->identityName]->id;
                $loggedOut = true;
            } else {
                $loggedOut = false;
            }

            session_destroy();
            unset($_SESSION);

            return $loggedOut;
        }
        
        return true;
    }
}
