<?php

require_once 'Inspector.php';

final class FunctionAnalyserInspector extends Inspector
{
    public function inspect(Cursor $cursor, Scope $scope)
    {
        $token = $cursor->getCurrent();

        if ($token->id == '__get') {
            $scope->previous->getInfo()->has_magic_get = true;
        } elseif ($token->id == '__set') {
            $scope->previous->getInfo()->has_magic_set = true;
        }
    }
}
