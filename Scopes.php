<?php

require_once 'Scope.php';

final class Scopes
{
    private $_scopes    = [];
    private $_cur_scope = null;

    public function __construct()
    {
        $this->pushScope(new Scope(0, 0));
    }

    public function getAllScopes()
    {
        return $this->_scopes;
    }

    public function getCurrentScope()
    {
        return $this->_cur_scope;
    }

    public function pushScope(Scope $scope)
    {
        $this->_cur_scope = $scope;
        array_push($this->_scopes, $scope);
    }

    public function popScope()
    {
        if ($this->_cur_scope->previous) {
            $this->_cur_scope = $this->_cur_scope->previous;
        }
    }
}
