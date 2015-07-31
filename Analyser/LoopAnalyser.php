<?php

require_once 'Analyser.php';

class LoopAnalyser extends VariableAnalyser
{
    const ID = 'LA';

    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrent();
        assert(
            $token->type == T_WHILE ||
            $token->type == T_DO ||
            $token->type == T_FOR ||
            $token->type == T_FOREACH
        );

        $scope = new Scope($token->line, $token->type, $scopes->getCurrentScope());
        $scopes->pushScope($scope);

        $approval = $this->getApprovalFor('Variable');

        $cursor->next(); // jump over loop token
        $tok = $cursor->getCurrent();

        while ($cursor->isValid() && !($tok->type == T_OPEN_CURLY || $tok->type == T_SEMICOLON)) {
            if ($approval->approve($cursor, $scope)) {
                if ($tok->id == '$this') {
                    $this->_analyseThisDecl($scopes, $cursor);
                    continue;
                }

                $var = new Variable($tok->id, $tok->line);
                $vp = $scope->findVariable($var);

                if ($vp) {
                    if ($vp->defined) {
                        $vp->usage++;

                        $this->_debug->log(self::ID, $token->line, Debug::VarExists, $vp->id, $vp->usage);
                    } else {
                        $this->_debug->log(self::ID, $token->line, Debug::VarExistsUndefined, $vp->id);
                    }
                } else {
                    $this->_debug->log(self::ID, $tok->line, Debug::VarNew, $tok->id);

                    $var->usage       = 0;
                    $var->initialized = $this->_isAlwaysInitialized($cursor) || $this->_findInitializer($cursor);
                    $var->defined     = true; // Default auf true
                    $var->reference   = $cursor->lookBehind()->type == T_AND;
                    $var->location    = 'loop:' . $token->sym;

                    $scope->addVariable($var);
                }
            }

            $cursor->next();
            $tok = $cursor->getCurrent();
        }

        return true;
    }
}
