<?php

define('DEBUG', false);
define('FORMAT', '%s on line %d');
define('DEBUG_FORMAT', '[%s] @ %d : %s');

if (DEBUG) {
    define('DEBUG_PRINT_FORMAT', '<pre>' . DEBUG_FORMAT . '</pre>');
    define('PRINT_FORMAT', '<pre>' . FORMAT . '</pre>' . PHP_EOL);
} else {
    define('DEBUG_PRINT_FORMAT', DEBUG_FORMAT . PHP_EOL);
    define('PRINT_FORMAT', FORMAT . PHP_EOL);
}

// just in case of a typo...
defined('DEBUG') or die('Typo?');
defined('FORMAT') or die('Typo?');
defined('DEBUG_FORMAT') or die('Typo?');
defined('PRINT_FORMAT') or die('Typo?');
defined('DEBUG_PRINT_FORMAT') or die('Typo?');
