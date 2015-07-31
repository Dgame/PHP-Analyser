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

        $cursor->pushPosition();
        $cursor->next(); // jump over T_OPEN_CURLY

        $curlies = 1;

        $tok = $cursor->getCurrent();
        while ($cursor->isValid() && $curlies > 0) {
            switch ($tok->type) {
                case T_USE:
                    $scope->getInfo()->use_traits = true;
                break;

                case T_OPEN_CURLY:
                    $curlies++;
                break;

                case T_CLOSE_CURLY:
                    $curlies--;
                break;
            }

            $cursor->next();
            $tok = $cursor->getCurrent();
        }

        $cursor->popPosition();
    }
}
