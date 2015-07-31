<?php

abstract class Approval
{
    abstract public function approve(Cursor $cursor, Scope $scope);

    public static function Create(string $name)
    {
        $name .= 'AnalyserApproval';
        require_once $name . '.php';

        return new $name();
    }
}
