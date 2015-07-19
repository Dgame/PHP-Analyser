<?php

require_once 'Property.php';

final class Scope
{
    public $usage = 0;

    private $_line      = 0;
    private $_token     = 0;
    private $_variables = [];
    private $_previous  = null;

    public function __construct(int $line, int $token, Scope $previous = null)
    {
        $this->_line     = $line;
        $this->_token    = $token;
        $this->_previous = $previous;
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
