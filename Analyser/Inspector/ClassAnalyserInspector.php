<?php

require_once 'Inspector.php';

final class ClassAnalyserInspector extends Inspector
{
    public function inspect(Cursor $cursor, Scope $scope)
    {
        $tok = $cursor->getCurrent();
        while ($cursor->isValid() && $tok->type != T_OPEN_CURLY) {
            if ($tok->type == T_EXTENDS) {
                $scope->getInfo()->is_child_class = true;
            } elseif ($tok->type == T_IMPLEMENTS) {
                $scope->getInfo()->has_interface = true;
            }

            $cursor->next();
            $tok = $cursor->getCurrent();
        }
    }
}
