<?php

final class ScopeInfo
{
    private $_info = [];

    public function __set(string $name, $value)
    {
        $this->_info[$name] = $value;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->_info)) {
            return $this->_info[$name];
        }

        return null;
    }
}
