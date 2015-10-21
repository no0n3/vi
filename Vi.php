<?php

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class Vi {
    public static $app;

    public static function autoload($className) {
        global $params;
        $className = str_replace('\\', '/', $className);
        include __DIR__ . "/$className.php";
    }

    public static function handleException($e) {
        ob_clean();
        Vi::$app->response->setContentType('text\html');

        function setError($code, $msg) {
            http_response_code($code);
            echo $msg;
        }

        if ($e instanceof \components\exceptions\ForbiddenException) {
            setError(403, 'FORBIDDEN');
        } else if ($e instanceof \components\exceptions\NotFoundException) {
            setError(404, 'NOT FOUND');
        } else if ($e instanceof \components\exceptions\WrongMethodException) {
            setError(405, 'WRONG METHOD');
        } else if ($e instanceof \components\exceptions\BadRequestException) {
            setError(400, 'BAD REQUEST');
        } else {
            setError(500, 'INTERNAL SERVER ERROR');
        }

        echo '<br/><br/>';
        echo "error_no = " . $e->getCode() . "<br/>";
        echo "error    = " . $e->getMessage() . "<br/>";
        echo "file     = " . $e->getFile() . "<br/>";
        echo "line     = " . $e->getLine() . "<br/>";
        echo "trace    = " . $e->getTraceAsString() . "<br/>";
    }

    public static function setErrorHandler() {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if ('dev' === VI_ENV) {
                if ($this->isConsoleApp) {
                    if (in_array($errno, [E_USER_WARNING, E_WARNING])) {
                        $errType = "WARNING";
                    } else if (in_array($errno, [E_USER_NOTICE, E_NOTICE])) {
                        $errType = "NOTICE";
                    } else {
                        $errType = "ERROR";
                    }
                } else {
                    $msg = <<<HTML
                <div style="width : 100%; border : 2px solid black;">
                    error_no = $errno<br/>
                    error    = $errstr<br/>
                    file     = $errfile<br/>
                    line     = $errline<br/>
                </div>
HTML;
                }

                throw new \components\exceptions\ErrorException($msg);
            }
        });
        set_exception_handler(function($e) {
            ob_clean();
            $this->response->setContentType('text\html');

            function setError($code, $msg) {
                http_response_code($code);
                echo $msg;
            }

            if ($e instanceof ForbiddenException) {
                setError(403, 'FORBIDDEN');
            } else if ($e instanceof WrongMethodException) {
                setError(405, 'WRONG METHOD');
            } else if ($e instanceof BadRequestException) {
                setError(400, 'BAD REQUEST');
            } else {
                setError(500, 'INTERNAL SERVER ERROR');
            }

            echo '<br/><br/>';
            echo "error_no = " . $e->getCode() . "<br/>";
            echo "error    = " . $e->getMessage() . "<br/>";
            echo "file     = " . $e->getFile() . "<br/>";
            echo "line     = " . $e->getLine() . "<br/>";
            echo "trace    = " . $e->getTraceAsString() . "<br/>";
        });
    }
}

spl_autoload_register('Vi::autoload');
