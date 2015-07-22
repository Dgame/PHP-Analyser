<?php

abstract class Approval
{
    abstract public function approve(Cursor $cursor, Scope $scope);

    public static function Create(string $class_name)
    {
        $class_name .= 'Approval';
        require_once $class_name . '.php';

        return new $class_name();
    }
}
