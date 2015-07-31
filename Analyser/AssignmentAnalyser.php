<?php

require_once 'Analyser.php';

class AssignmentAnalyser extends VariableAnalyser
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

        $approval = $this->getApprovalFor('Variable');
        $scope = $scopes->getCurrentScope();

        $moved = false;
        while ($cursor->isValid() && $tok->type != T_SEMICOLON) {
            if ($approval->approve($cursor, $scope)) {
                if ($tok->id == '$this') {
                    $this->_analyseThisDecl($scopes, $cursor);
                    continue;
                }
                
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
