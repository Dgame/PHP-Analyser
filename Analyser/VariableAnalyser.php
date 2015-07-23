<?php

require_once 'Analyser.php';

class VariableAnalyser extends Analyser
{
    const ID = 'VA';

    const Dollar       = '$';
    const PropertyThis = '$this';

    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrent();
        assert($token->type == T_VARIABLE);

        $scope = $scopes->getCurrentScope();

        if (!$this->getApproval()->approve($cursor, $scope)) {
            return false;
        }

        if ($token->id == self::PropertyThis) {
            return $this->_analyseThisDecl($scopes, $cursor);
        }

        $var = new Variable($token->id, $token->line);
        $vp  = $scope->findVariable($var);

        if ($vp) {
            if ($vp->defined) {
                $vp->usage++;

                $this->_debug->log(self::ID, $token->line, Debug::VarExists, $vp->id, $vp->usage);
            } else {
                $this->_debug->log(self::ID, $token->line, Debug::VarExistsUndefined, $vp->id);
            }
        } else {
            $this->_debug->log(self::ID, $token->line, Debug::VarNew, $token->id);

            $var->usage       = 0;
            $var->initialized = $this->_isAlwaysInitialized($cursor) || $this->_findInitializer($cursor);
            $var->defined     = true; // Default auf true
            $var->reference   = $cursor->lookBehind()->type == T_AND;

            $scope->addVariable($var);
        }

        $cursor->next(); // jump over variable

        return true;
    }

    private function _analyseThisDecl(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrent();
        assert($token->id == self::PropertyThis);

        $cursor->next(); // jump over '$this'
        $tok = $cursor->getCurrent();
        if ($tok->type == T_OBJECT_OPERATOR) {
            $cursor->next(); // jump over '->'
            $tok = $cursor->getCurrent();

            $scope = $scopes->getCurrentScope();

            assert($tok->type == T_STRING);

            $var = new Variable(self::Dollar . $tok->id, $token->line);
            $vp  = $scope->findVariable($var);

            if ($vp) {
                if ($vp->defined) {
                    $vp->usage++;

                    $this->_debug->log(self::ID, $token->line, Debug::PropertyExists, $vp->id, $vp->usage);
                } else {
                    $this->_debug->log(self::ID, $token->line, Debug::PropertyExistsUndefined, $vp->id);
                }
            } else {
                $this->_debug->log(self::ID, $token->line, Debug::PropertyNew, $tok->id);

                $var->defined     = false;
                $var->property    = true;
                $var->initialized = $this->_findInitializer($cursor);

                $scope->addVariable($var);
            }
        }

        return true;
    }
}
