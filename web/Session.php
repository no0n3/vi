<?php
namespace web;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class Session {

    private function __construct() {
        // prevent public instantiation.
    }

    public static function getInstance() {
        static $inst = null;

        if (null === $inst) {
            $inst = new self();
        }

        return $inst;
    }

    public function get($name = null) {
        return null === $name ? $_SESSION : (isset($_SESSION[$name]) ? $_SESSION[$name] : null);
    }

    public function set($name, $value) {
        return $_SESSION[$name] = $value;
    }

    public function start() {
        
    }

    public function destroy() {
        
    }

}
