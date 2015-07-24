<?php

require_once 'Property.php';
require_once 'ScopeInfo.php';

final class Scope
{
    public $usage = 0;

    private $_line  = 0;
    private $_token = 0;

    private $_variables = [];
    private $_previous  = null;
    private $_info      = null;

    public function __construct(int $line, int $token, Scope $previous = null)
    {
        // the global scope
        if ($line == 0) {
            $this->usage = 1;
        }

        $this->_line     = $line;
        $this->_token    = $token;
        $this->_previous = $previous;

        $this->_info = new ScopeInfo();
    }

    public function getInfo()
    {
        return $this->_info;
    }

    public function findPrevious(int $token)
    {
        $scope = $this->previous;
        while ($scope) {
            if ($scope->token == $token) {
                return $scope;
            }
            $scope = $scope->previous;
        }

        return null;
    }

    public function addVariable(Variable $var)
    {
        $this->_variables[$var->name] = $var;
    }

    public function findVariable(Variable $v)
    {
        $scope = $this;
        do {
            $var = $scope->getVariable($v->name);
            if ($var) {
                return $var;
            }
            $scope = $scope->previous;
        } while ($scope);

        return null;
    }

    public function getVariable(string $name)
    {
        if (array_key_exists($name, $this->_variables)) {
            return $this->_variables[$name];
        }

        return null;
    }

    use Property;
}
