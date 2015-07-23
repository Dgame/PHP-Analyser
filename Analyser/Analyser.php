<?php

require_once 'Approval/Approval.php';
require_once 'Inspector/Inspector.php';

function __autoload($class_name)
{
    static $directories = [
        'Analyser',
        'Analyser' . DIRECTORY_SEPARATOR . 'Inspector',
        'Analyser' . DIRECTORY_SEPARATOR . 'Approval',
    ];

    foreach ($directories as $dir) {
        $filename = PHP_ANALYSER_PATH . $dir . DIRECTORY_SEPARATOR . $class_name . '.php';
        if (file_exists($filename)) {
            require_once $filename;
            break;
        }
    }
}

require_once PHP_ANALYSER_PATH . 'Detector.php';
require_once PHP_ANALYSER_PATH . 'Variable.php';
require_once PHP_ANALYSER_PATH . 'Debug.php';

abstract class Analyser
{
    private $_approval  = null;
    private $_inspector = null;

    protected $_detector = null;
    protected $_debug = null;

    public function __construct(Detector $detector, int $options)
    {
        $this->_detector = $detector;
        $this->_debug = new Debug($options);
    }

    final public function getApproval()
    {
        if (!$this->_approval) {
            $this->_approval = Approval::Create(get_called_class());
        }

        return $this->_approval;
    }

    final public function getInspector()
    {
        if (!$this->_inspector) {
            $this->_inspector = Inspector::Create(get_called_class());
        }

        return $this->_inspector;
    }

    abstract public function analyse(Scopes $scopes, Cursor $cursor);

    final protected function _findInitializer(Cursor $cursor)
    {
        $cursor->pushPosition();

        $tok = $cursor->getCurrent();
        for (; $cursor->isValid() && $tok->type != T_SEMICOLON; $cursor->next()) {
            if (Parser::IsAssignment($tok)) {
                $cursor->popPosition();

                return true;
            }

            $tok = $cursor->getCurrent();
        }

        $cursor->popPosition();

        return false;
    }

    final protected function _isAlwaysInitialized(Cursor $cursor)
    {
        $token = $cursor->getCurrent();
        assert($token->type == T_VARIABLE);

        // look ahead
        $next = $cursor->lookAhead();
        if ($next->type == T_CLOSE_PAREN || $next->type == T_DOUBLE_ARROW) {
            // it is in an foreach statement, e.g. foreach ($arr as $var) or foreach ($arr as $v => $k)
            return true;
        }

        return false;
    }
}
