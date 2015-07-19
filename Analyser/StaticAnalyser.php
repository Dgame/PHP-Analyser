<?php

require_once 'Analyser.php';

class StaticAnalyser extends Analyser
{
    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrentToken();
        assert($token->id == 'self' || $token->id == 'static' || $token->id == 'parent');

        $cursor->next(); // jump over
        $tok = $cursor->getCurrentToken();

        if ($tok->type == T_DOUBLE_COLON) { // self/static/parent access
            $cursor->next();
            $tok = $cursor->getCurrentToken();

            assert($tok->type == T_VARIABLE);

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
