<?php

require_once 'Analyser.php';

class AssignmentAnalyser extends Analyser
{
    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrentToken();
        assert(parent::IsAssignment($token));

        $cursor->next(); // jump over assignment
        $tok = $cursor->getCurrentToken();

        $moved = false;
        do {
            if ($tok->type == T_VARIABLE) {
                $scope = $scopes->getCurrentScope();

                $var = new Variable($tok->id, $tok->line);
                $vp  = $scope->findVariable($var);

                if ($vp) {
                    $vp->usage++;
                    $vp->defined = true;

                    if ($this->_options & (Options::Verbose | Options::Debug)) {
                        print '<pre>[AA] ' . $tok->line . ' : Found existing Variable ' . $vp->id  . ' increase usage: ' . $vp->usage;
                    }
                } else {
                    if ($this->_options & (Options::Verbose | Options::Debug)) {
                        print '<pre>[AA] ' . $tok->line . ' : Found new Variable ' . $tok->id;
                    }

                    $var->assignment = true;
                    $var->defined    = false;

                    $scope->addVariable($var);
                }
            }

            $moved = true;

            $cursor->next();
            $tok = $cursor->getCurrentToken();
        } while ($cursor->isValid() && $tok->id != ';');

        return $moved;
    }
}
