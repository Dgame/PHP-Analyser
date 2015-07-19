<?php

require_once 'Analyser.php';

class VariableAnalyser extends Analyser
{
    private static $Properties = [
        T_PRIVATE   => true,
        T_PROTECTED => true,
        T_PUBLIC    => true,
        T_STATIC    => true
    ];

    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrentToken();
        assert($token->type == T_VARIABLE);

        if ($token->id == '$this') {
            return $this->_analyseThisDecl($scopes, $cursor);
        }

        if ($token->id == 'self' || $token->id == 'static') {
            return $this->_analyseStaticDecl($scopes, $cursor);
        }

        if (Variable::IsException($token->id)) {
            return false; // don't match super globals, they are magic
        }

        // look behind
        if (array_key_exists($cursor->lookBehind()->type, self::$Properties)) {
            return false;// Properties are already visited
        }

        $scope = $scopes->getCurrentScope();

        $var = new Variable($token->id, $token->line);
        $vp  = $scope->findVariable($var);

        if ($vp) {
            $vp->usage++;
            if ($this->_options & (Options::Verbose | Options::Debug)) {
                $msg = 'Found existing Variable ' . $vp->id  . ' increase usage: ' . $vp->usage;
                printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
            }
        } else {
            if ($this->_options & (Options::Verbose | Options::Debug)) {
                $msg = 'Found new Variable ' . $token->id;
                printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
            }

            $var->usage       = 0;
            $var->initialized = $this->_findInitializer($cursor);
            $var->defined     = true; // Default auf true

            $scope->addVariable($var);
        }

        $cursor->next(); // jump over variable

        return true;
    }

    private function _analyseThisDecl(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrentToken();
        assert($token->id == '$this');

        $cursor->next(); // jump over '$this'
        $tok = $cursor->getCurrentToken();
        if ($tok->type == T_OBJECT_OPERATOR) {
            $cursor->next(); // jump over '->'

            // look ahead
            if ($cursor->lookAhead()->id == '(') {
                return; // it's a function call
            }

            $tok   = $cursor->getCurrentToken();
            $scope = $scopes->getCurrentScope();

            assert($tok->type == T_STRING);

            $var = new Variable('$' . $tok->id, $token->line);
            $vp  = $scope->findVariable($var);

            if ($vp) {
                $vp->usage++;
                if ($this->_options & (Options::Verbose | Options::Debug)) {
                    $msg = 'Found existing Property ' . $vp->id  . ' increase usage: ' . $vp->usage;
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

    private function _analyseStaticDecl(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrentToken();
        assert($token->id == 'self' || $token->id == 'static');

        $cursor->next(); // jump over 'self'
        $tok = $cursor->getCurrentToken();

        if ($tok->type == T_DOUBLE_COLON) { // self/static access
            $cursor->next();
            $tok = $cursor->getCurrentToken();

            assert($tok->id == T_VARIABLE);

            $scope = $scopes->getCurrentScope();

            $var = new Variable($tok->id, $token->line);
            $vp  = $scope->findVariable($var);

            if ($vp) {
                $vp->usage++;
                if ($this->_options & (Options::Verbose | Options::Debug)) {
                    $msg = 'Found existing static Property ' . $vp->id  . ' increase usage: ' . $vp->usage;
                    printf(DEBUG_PRINT_FORMAT, 'VA', $token->line, $msg);
                }
            } else {
                if ($this->_options & (Options::Verbose | Options::Debug)) {
                    $msg = 'Found new static Property ' . $tok->id;
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
