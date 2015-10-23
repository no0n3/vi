<?php
namespace web;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class User {

    const LOGGED_USER = 'user';

    const DEFAULT_USERNAME_FILED = 'username';
    const DEFAULT_PASSWORD_FILED = 'password';

    public $identity;
    public $identityName = self::LOGGED_USER;
    public $loginData;
    public $additionalUserData = [];

    public function __construct() {
        $this->identity = isset($_SESSION[$this->identityName]) ? $_SESSION[$this->identityName] : null;
    }

    public function setLoginData() {
        if (empty($this->loginData)) {
            $this->loginData = [
                'username' => self::DEFAULT_USERNAME_FILED,
                'password' => self::DEFAULT_PASSWORD_FILED
            ];
        } else {
            $this->loginData['username'] = isset($this->loginData['username']) ? $this->loginData['username'] : self::DEFAULT_USERNAME_FILED;
            $this->loginData['password'] = isset($this->loginData['password']) ? $this->loginData['password'] : self::DEFAULT_PASSWORD_FILED;
        }
    }

    /**
     * Checks if a user is logged in the current session.
     * @return boolean True if the user is logged, false otherwise.
     */
    public function isLogged() {
        return !empty(\Vi::$app->session->get($this->identityName));
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
        if ($this->isLogged()) {
            return true;
        }

        $fieldsArray = array_merge($this->loginData, $this->additionalUserData);

        $fileds = \helpers\ArrayHelper::getArrayToString(
            $fieldsArray,
            ',',
            function($v) {
                return "`$v`";
            }
        );

        $result = (new \db\Query())->select($fileds)
                ->from('users')
                ->where([
                   'email' => $email
                ])
                ->asAssoc()
                ->all();

        if (!empty($result)) {
            $result = $result[0];

            $loginSuccess = \Security::verifyHash($password, $result[$this->loginData['password']]);

            if ($loginSuccess) {
                unset($result[$this->loginData['password']]);

                $cls = \Vi::$app->user->identityClass;
                $logedUser = new $cls();

                foreach ($result as $key => $value) {
                    $logedUser->$key = $value;
                }

                $this->identity = $_SESSION[\Vi::$app->user->identityName] = $logedUser;
                $_SESSION['_csrf'] = sprintf("%d-%s", $logedUser->id, uniqid());
            }
        } else {
            $loginSuccess = false;
        }

        return $loginSuccess;
    }

    public function signUp($properties) {
        $a1 = [];
        $a2 = [];

        foreach ($properties as $name => $value) {
            $a1[$name] = ":$name";
            $a2[":$name"] = ($this->loginData['password'] === $name) ? \Security::hash($value) : $value;
        }

        return (new \db\Query())
            ->insert('users', $a1, $a2)
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

            unset($_SESSION);
            session_destroy(); 

            return $loggedOut;
        }

        return true;
    }
}
