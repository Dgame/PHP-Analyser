<?php

require_once 'Analyser.php';

class AssignmentAnalyser extends Analyser
{
    const ID = 'AA';

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
        while ($cursor->isValid() && $tok->type != T_SEMICOLON) {
            if ($tok->type == T_VARIABLE && Variable::Approve($tok->id)) {
                $scope = $scopes->getCurrentScope();

                $var = new Variable($tok->id, $tok->line);
                $vp  = $scope->findVariable($var);

                if ($vp) {
                    if ($vp->defined) {
                        $vp->usage++;
                        $vp->defined = true;

                        $this->_debug->log(self::ID, $tok->line, Debug::VarExists, $vp->id, $vp->usage);
                    } else {
                        $this->_debug->log(self::ID, $tok->line, Debug::VarExistsUndefined, $vp->id);
                    }
                } else {
                    $this->_debug->log(self::ID, $tok->line, Debug::VarNew, $tok->id);

                    $var->assignment = true;
                    $var->defined    = false;

                    $scope->addVariable($var);
                }
            }

            $moved = true;

            $cursor->next();
            $tok = $cursor->getCurrent();
        }

        return $moved;
    }
}
