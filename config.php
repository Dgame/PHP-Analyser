<?php

define('DEBUG', false);
define('FORMAT', '%s on line %d');

if (DEBUG) {
    define('PRINT_FORMAT', '<pre>' . FORMAT . '</pre>' . PHP_EOL);
} else {
    define('PRINT_FORMAT', FORMAT . PHP_EOL);
}

// just in case of a typo...
defined('DEBUG') or die('Typo?');
defined('FORMAT') or die('Typo?');
defined('PRINT_FORMAT') or die('Typo?');
