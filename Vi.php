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

        if ($e instanceof \exceptions\ForbiddenException) {
            setError(403, 'FORBIDDEN');
        } else if ($e instanceof \exceptions\NotFoundException) {
            setError(404, 'NOT FOUND');
        } else if ($e instanceof \exceptions\WrongMethodException) {
            setError(405, 'WRONG METHOD');
        } else if ($e instanceof \exceptions\BadRequestException) {
            setError(400, 'BAD REQUEST');
        } else {
            setError(500, 'INTERNAL SERVER ERROR');
        }

        if ('prod' !== VI_ENV) {
            echo '<br/><br/>';
            echo "error_no = " . $e->getCode() . "<br/>";
            echo "error    = " . $e->getMessage() . "<br/>";
            echo "file     = " . $e->getFile() . "<br/>";
            echo "line     = " . $e->getLine() . "<br/>";
            echo "trace    = " . $e->getTraceAsString() . "<br/>";
        }
    }
}

spl_autoload_register('Vi::autoload');
