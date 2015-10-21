<?php

use Vi;
use components\web\Request;
use components\web\Response;
use components\web\Session;
use components\Security;
use components\exceptions\ForbiddenException;
use components\exceptions\WrongMethodException;
use components\exceptions\BadRequestException;
use components\exceptions\NotFoundException;
use components\web\Controller;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class App extends \base\Object {

    private static $inst;

    private $user;
    private $request;
    private $response;
    private $session;

    public $params;
    private $components;

    private $controller;
    private $isConsoleApp;

    public function __get($name) {
        static $comps = [];
        if ($this->hasProperty($name)) {
            if (!isset($comps[$name])) {
                $comps[$name] = true;

                foreach (isset($this->components[$name]) ? $this->components[$name] : [] as $prop => $value) {
                    $this->$name->$prop = $value;
                }
            }

            return $this->$name;
        } else if (isset($this->components[$name])) {
            if (!isset($comps[$name])) {
                $comps[$name] = new $this->components[$name]['class']();
            }
            foreach ($this->components[$name] as $prop => $value) {
                if ('class' === $prop) {
                    continue;
                }
                $comps[$name]->$prop = $value;
            }
            return $comps[$name];
        }
        return null;
    }

    public static function getInst() {
        if (null === self::$inst) {
            self::$inst = new self();
        }
        return self::$inst;
    }

    public static function run($config = [], $isConsoleApp = false) {
        static $inst = null;
        if (null === $inst) {
            $inst = new self($isConsoleApp);

            Vi::$app = $inst;

            if (!is_array($config) || empty($config)) {
                $config = [];
            }

            $inst->components = isset($config['components']) ? $config['components'] : [];
            $inst->params = isset($config['params']) ? $config['params'] : [];

            if (!$isConsoleApp) {
                $inst->dispatch($inst->getPath(
                    isset($route) ? $route : $_SERVER['PATH_INFO']
                ));
            }
        }
    }

    private function __construct($isConsoleApp) {
        $this->isConsoleApp = $isConsoleApp;

        if (!$this->isConsoleApp) {
            session_start();
            $this->user = new \components\web\User();
        }

        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
        $this->session = Session::getInstance();

        $this->setErrorHandlers();
    }

    private function setErrorHandlers() {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
//            if ('dev' === VI_ENV) {
//                if ($this->isConsoleApp) {
//                    if (in_array($errno, [E_USER_WARNING, E_WARNING])) {
//                        $errType = "WARNING";
//                    } else if (in_array($errno, [E_USER_NOTICE, E_NOTICE])) {
//                        $errType = "NOTICE";
//                    } else {
//                        $errType = "ERROR";
//                    }
//                } else {
                    $msg = <<<HTML
                <div style="width : 100%; border : 2px solid black;">
                    error_no = $errno<br/>
                    error    = $errstr<br/>
                    file     = $errfile<br/>
                    line     = $errline<br/>
                </div>
HTML;
//                }
//            }

            throw new \components\exceptions\ErrorException();
        });

        set_exception_handler(function($e) {
            if (null !== $this->controller) {
                $this->controller->error($e);
            } else {
                Vi::handleException($e);
            }
        });
    }

    private function getPath($tp) {
        $a = explode('/', $tp);
        $a1 = [];
        $route = [];

        foreach ($a as $path) {
            if (!empty($path)) {
                $a1[] = $path;
            }
        }

        $c = count($a1);

        if ($c === 0) {
            $route['contr'] = 'site';
            $route['action'] = 'error';
        } elseif ($c === 1) {
            $route['contr'] = $a1[0];
            $route['action'] = Controller::DEFAULT_ACTION;
        } elseif ($c >= 2) {
            $route['contr'] = $a1[0];
            $route['action'] = $a1[1];
        }

        return $route;
    }

    public function dispatch($route) {
        if (is_string($route)) {
            $route = $this->getPath($route);
        }

        $contrId = $contrName = $route['contr'];
        $contrName[0] = chr(ord($contrName) ^ 32);
        $action = $actionName = $route['action'];
        $actionName[0] = chr(ord($actionName) ^ 32);
        $controllerClass = "\\controllers\\{$contrName}Controller";
        $actionMethod = "do{$actionName}";

        if (!class_exists($controllerClass)) {
            throw new NotFoundException();
        }

        $this->controller = new $controllerClass($contrId, $action);

        if (!method_exists($this->controller, $actionMethod)) {
            throw new NotFoundException();
        }

        $rules = $this->controller->rules();

        $actionRules = isset($rules[$action]) ? $rules[$action] :
            (isset($rules['*']) ? $rules['*'] : []);

        if ($actionRules) {
            if (isset($actionRules['response_type'])) {
                $this->controller->responseType = $actionRules['response_type'];

                $this->response->setContentType($actionRules['response_type']);
            }

            if (isset($actionRules['methods']) &&
                !in_array(strtolower($_SERVER['REQUEST_METHOD']), $actionRules['methods'])
            ) {
                throw new WrongMethodException();
            }

            if (isset($actionRules['roles']) &&
                in_array(Controller::REQUIRED_LOGIN, $actionRules['roles']) &&
                !$this->user->inRole($actionRules['roles'])
            ) {
                if (!$this->request->isAjax()) {
                    if (empty($this->controller->notInRole($action, $actionRules['roles']))) {
                        throw new ForbiddenException();
                    }
                    return;
                }

                throw new ForbiddenException();
            }
        }

        if ($this->controller->hasCsrfValidation &&
            (
                !$this->request->param('_csrf') ||
                !Security::verifyHash($this->session->get('_csrf'), $this->request->param('_csrf'))
            )
        ) {
            throw new ForbiddenException();
        }

        if (!$this->controller->beforeAction($action)) {
            throw new NotFoundException();
        }

        $filters = [];

        foreach (isset($actionRules['filters']) ? $actionRules['filters'] : [] as $filterClass) {
            $filter = new $filterClass();

            if (!$filter->beforeAction($action)) {
                throw new NotFoundException();
            }

            $filters[] = $filter;
        }

        $view = $this->controller->{$actionMethod}();

        $this->controller->afterAction();

        foreach ($filters as $filter) {
            $filter->afterAction();
        }

        if ($this->controller->responseType === 'text\html') {
            if ($view) {
                $layout = null === $this->controller->layout ? \components\web\Controller::DEFAULT_LAYOUT : $this->controller->layout;

                echo $this->controller->view->renderFile(
                    Vi::$app->params['sitePath'] . "/views/layouts/$layout.php",
                    ['view' => $view]
                );
            }
        } else {
            echo $view;
        }
    }

}
