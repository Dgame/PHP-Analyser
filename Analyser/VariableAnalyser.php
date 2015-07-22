<?php

require_once 'Analyser.php';

class VariableAnalyser extends Analyser
{
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

        if ($token->id == '$this') {
            return $this->_analyseThisDecl($scopes, $cursor);
        }

        $var = new Variable($token->id, $token->line);
        $vp  = $scope->findVariable($var);

        if ($vp) {
            if ($vp->defined) {
                $vp->usage++;
                if ($this->_options & (Options::Verbose | Options::Debug)) {
                    $msg = 'Found existing Variable ' . $vp->id . ' increase usage: ' . $vp->usage;
                    printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
                }
            } elseif ($this->_options & (Options::Verbose | Options::Debug)) {
                $msg = 'Found existing but undefined Variable ' . $vp->id;
                printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
            }
        } else {
            if ($this->_options & (Options::Verbose | Options::Debug)) {
                $msg = 'Found new Variable ' . $token->id;
                printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
            }

            $var->usage       = 0;
            $var->initialized = $this->_isAlwaysInitialized($cursor) || $this->_findInitializer($cursor);
            $var->defined     = true; // Default auf true

            $scope->addVariable($var);
        }

        $cursor->next(); // jump over variable

        return true;
    }

    private function _analyseThisDecl(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrent();
        assert($token->id == '$this');

        $cursor->next(); // jump over '$this'
        $tok = $cursor->getCurrent();
        if ($tok->type == T_OBJECT_OPERATOR) {
            $cursor->next(); // jump over '->'
            $tok = $cursor->getCurrent();

            $scope = $scopes->getCurrentScope();

            assert($tok->type == T_STRING);

            $var = new Variable('$' . $tok->id, $token->line);
            $vp  = $scope->findVariable($var);

            if ($vp) {
                if ($vp->defined) {
                    $vp->usage++;
                    if ($this->_options & (Options::Verbose | Options::Debug)) {
                        $msg = 'Found existing Property ' . $vp->id . ' increase usage: ' . $vp->usage;
                        printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
                    }
                } elseif ($this->_options & (Options::Verbose | Options::Debug)) {
                    $msg = 'Found existing but undefined property ' . $vp->id;
                    printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
                }
            } else {
                if ($this->_options & (Options::Verbose | Options::Debug)) {
                    $msg = 'Found new Property ' . $tok->id;
                    printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
                }

                $var->defined     = false;
                $var->property    = true;
                $var->initialized = $this->_findInitializer($cursor);

                $scope->addVariable($var);
            }
        }

        return true;
    }
}
