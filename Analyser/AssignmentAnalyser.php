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
        $token = $cursor->getCurrent();
        assert(Parser::IsAssignment($token));

        $cursor->next(); // jump over assignment
        $tok = $cursor->getCurrent();

        $moved = false;
        do {
            if ($tok->type == T_VARIABLE && Variable::Approve($tok->id)) {
                $scope = $scopes->getCurrentScope();

                $var = new Variable($tok->id, $tok->line);
                $vp  = $scope->findVariable($var);

                if ($vp) {
                    if ($vp->defined) {
                        $vp->usage++;
                        $vp->defined = true;

                        if ($this->_options & (Options::Verbose | Options::Debug)) {
                            $msg = 'Found existing Variable ' . $vp->id . ' increase usage: ' . $vp->usage;
                            printf(DEBUG_PRINT_FORMAT, 'AA', $tok->line, $msg);
                        }
                    } elseif ($this->_options & (Options::Verbose | Options::Debug)) {
                        $msg = 'Found existing but undefined Variable ' . $vp->id;
                        printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
                    }
                } else {
                    if ($this->_options & (Options::Verbose | Options::Debug)) {
                        $msg = 'Found new Variable ' . $tok->id;
                        printf(DEBUG_PRINT_FORMAT, 'AA', $tok->line, $msg);
                    }

                    $var->assignment = true;
                    $var->defined    = false;

                    $scope->addVariable($var);
                }
            }

            $moved = true;

            $cursor->next();
            $tok = $cursor->getCurrent();
        } while ($cursor->isValid() && $tok->type != T_SEMICOLON);

        return $moved;
    }
}
