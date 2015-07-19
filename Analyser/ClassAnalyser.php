<?php

require_once 'Analyser.php';

class ClassAnalyser extends Analyser
{
    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrentToken();
        assert($token->type == T_CLASS);

        $scope = new Scope($token->line, T_CLASS, $scopes->getCurrentScope());
        $scopes->pushScope($scope);

        $cursor->next(); // jump over T_CLASS
        $token = $cursor->getCurrentToken(); // store the class name
        $cursor->skipUntil('{');

        $cursor->pushPosition();
        $tok = $cursor->getCurrentToken();

        do {
            switch ($tok->type) {
                case T_STATIC:
                case T_PRIVATE:
                case T_PUBLIC:
                case T_PROTECTED:
                    $prot = $tok->type;
                    $stat = $this->_checkStaticProperty($cursor, $prot);

                    $cursor->next(); // jump over protection
                    $tok = $cursor->getCurrentToken();

                    if ($tok->type == T_VARIABLE) {
                        if ($this->_options & (Options::Verbose | Options::Debug)) {
                            $msg = 'Found new Property ' . $tok->id;
                            printf(DEBUG_PRINT_FORMAT, 'CA', $tok->line, $msg);
                        }

                        $var              = new Variable($tok->id, $tok->line);
                        $var->protection  = $prot;
                        $var->location    = 'class:' . $token->id;
                        $var->property    = true;
                        $var->usage       = 0;
                        $var->initialized = $this->_findInitializer($cursor);
                        $var->defined     = true; // Default auf true
                        $var->state       = $stat;

                        $scope->addVariable($var);
                    }
                break;
            }

            $cursor->next();
            $tok = $cursor->getCurrentToken();
        } while ($cursor->isValid());

        $cursor->popPosition();

        return true;
    }

    private function _checkStaticProperty(Cursor $cursor, int& $protection)
    {
        $token = $cursor->getCurrentToken();

        if ($token->type == T_STATIC) {
            $protection = $cursor->lookBehind()->type;

            return T_STATIC;
        }

        if ($cursor->lookAhead()->type == T_STATIC) {
            $cursor->next(); // jump over static

            return T_STATIC;
        }

        return 0;
    }
}
