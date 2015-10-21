<?php
namespace components\web;

use Vi;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
abstract class Controller extends \base\Object {

    const DEFAULT_ACTION = 'index';
    const DEFAULT_LAYOUT = 'main';

    const REQUIRED_LOGIN = '!';
    const ALL = '*';
    const NOT_LOGGED = '@';

    public $layout = self::DEFAULT_LAYOUT;
    public $hasCsrfValidation = false;

    public $view;
    public $id;

    /**
     * The action response type.
     * @var string
     */
    public $responseType = 'text\html';

    function __construct($id, $actionId) {
        $this->view = new \components\web\View($this);
        $this->id = $id;
    }

    public function rules() {
        return [];
    }

    /**
     * Gets called before the execution of every action.
     * @param string $actionId the id of the action to be called.
     * @return boolean
     */
    public function beforeAction($actionId) {
        return true;
    }

    /**
     * Gets called after the execution of every action.
     */
    public function afterAction() {
    }

    public function notInRole($action, $roles) {
        return false;
    }

    public function error($e) {
        Vi::handleException($e);
    }

    protected function render($viewName, $vars = []) {
        return $this->view->render(
            $viewName,
            $vars
        );
    }

    public function forward($path) {
        ob_clean();
        Vi::$app->dispatch($path);
    }

    protected final function redirect($path) {
        header("Location: $path");
    }

}
