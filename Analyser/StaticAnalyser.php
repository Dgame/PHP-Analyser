<?php

require_once 'Analyser.php';

class StaticAnalyser extends Analyser
{
    const ID = 'SA';

    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrent();
        assert($token->type == T_SELF || $token->type == T_PARENT || $token->type == T_STATIC);

        $cursor->next(); // jump over
        $tok = $cursor->getCurrent();

        if ($tok->type == T_DOUBLE_COLON) {
            // self/static/parent access
            $cursor->next();
            $tok = $cursor->getCurrent();

            if ($tok->type != T_VARIABLE) {
                return true;
            }

            $scope = $scopes->getCurrentScope();

            $var = new Variable($tok->id, $token->line);
            $vp  = $scope->findVariable($var);

            if ($vp) {
                $vp->usage++;

                $this->_debug->log(self::ID, $token->line, Debug::PropertyStaticExists, $vp->id, $vp->usage);
            } else {
                $this->_debug->log(self::ID, $token->line, Debug::PropertyStaticNew, $var->id);

                $var->defined     = false;
                $var->property    = true;
                $var->initialized = $this->_findInitializer($cursor);

                $scope->addVariable($var);
            }
        }

        return true;
    }
}
