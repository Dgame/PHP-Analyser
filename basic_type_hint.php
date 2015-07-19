<?php

error_reporting(E_ALL | E_STRICT);

if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    define('BASIC_TYPE_HINT_REGEX', '#^Argument \d+ passed to .+? must be an instance of ([a-z]+[\w_\\\]*), ([a-z]+) given#i');

    function basic_type_hint($err_lvl, $err_msg)
    {
        if ($err_lvl === E_RECOVERABLE_ERROR) {
            if (preg_match(BASIC_TYPE_HINT_REGEX, $err_msg, $matches)) {
                if (strpos($matches[1], '\\') !== false) {
                    $arr        = explode('\\', $matches[1]);
                    $matches[1] = array_pop($arr);
                }

                if ($matches[1] === $matches[2]) {
                    return true;
                }

                switch ($matches[1]) {
                    case 'int':
                        return $matches[2] === 'integer';
                    case 'bool':
                        return $matches[2] === 'boolean';
                    case 'float':
                        return $matches[2] === 'double';
                    default:
                        return false;
                }
            }
        }

        return false;
    }

    set_error_handler('basic_type_hint');
}
