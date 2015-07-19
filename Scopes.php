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

    public function open()
    {
        $this->_cur_scope->usage++;
    }

    public function close()
    {
        $this->_cur_scope->usage--;
        if ($this->_cur_scope->usage <= 0) {
            $this->popScope();
        }
    }

    public function popScope()
    {
        $this->_cur_scope = $this->_cur_scope->previous;
    }
}
