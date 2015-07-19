<?php

function __autoload($class_name)
{
    require_once 'Analyser/' . $class_name . '.php';
}

require_once 'Detector.php';
require_once 'Variable.php';
require_once 'Options.php';

abstract class Analyser
{
    private static $Assignments = [
        T_AND_EQUAL    => true,
        T_OR_EQUAL     => true,
        T_CONCAT_EQUAL => true,
        T_DOUBLE_ARROW => true,
        T_MUL_EQUAL    => true,
        T_DIV_EQUAL    => true,
        T_MOD_EQUAL    => true,
        T_PLUS_EQUAL   => true,
        T_MINUS_EQUAL  => true,
        T_XOR_EQUAL    => true,
    ];

    public static function IsAssignment(Token $token)
    {
        return array_key_exists($token->type, self::$Assignments) || $token->id == '=';
    }

    protected $_detector = null;
    protected $_options  = 0;

    public function __construct(Detector $detector, int $options)
    {
        $this->_detector = $detector;
        $this->_options  = $options;
    }

    abstract public function analyse(Scopes $scopes, Cursor $cursor);

    final protected function _findInitializer(Cursor $cursor)
    {
        $cursor->pushPosition();

        $tok = $cursor->getCurrentToken();
        for (; $cursor->isValid() && $tok->id != ';'; $cursor->next()) {
            if (self::IsAssignment($tok)) {
                $cursor->popPosition();

                return true;
            }

            $tok = $cursor->getCurrentToken();
        }

        $cursor->popPosition();

        return false;
    }
}
