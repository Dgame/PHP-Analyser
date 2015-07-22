<?php

abstract class Inspector
{
    abstract public function inspect(Cursor $cursor, Scope $scope);

    public static function Create(string $class_name)
    {
        $class_name .= 'Inspector';
        require_once $class_name . '.php';

        return new $class_name();
    }
}
