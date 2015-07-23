<?php

require_once 'Analyser.php';

class ClassAnalyser extends Analyser
{
    const ID = 'CA';

    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrent();
        assert($token->type == T_CLASS);

        $scope = new Scope($token->line, T_CLASS, $scopes->getCurrentScope());
        $scopes->pushScope($scope);

        $cursor->next(); // jump over T_CLASS
        $token = $cursor->getCurrent(); // store the class name

        $this->getInspector()->inspect($cursor, $scope);

        $cursor->pushPosition();
        $tok = $cursor->getCurrent();

        do {
            switch ($tok->type) {
                case T_STATIC:
                case T_PRIVATE:
                case T_PUBLIC:
                case T_PROTECTED:
                    $prot = $tok->type;
                    $stat = $this->_isStaticProperty($cursor, $prot);

                    $cursor->next(); // jump over protection
                    $tok = $cursor->getCurrent();

                    if ($tok->type == T_VARIABLE) {
                        $this->_debug->log(self::ID, $tok->line, Debug::PropertyNew, $tok->id);

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
            $tok = $cursor->getCurrent();
        } while ($cursor->isValid());

        $cursor->popPosition();

        return true;
    }

    private function _isStaticProperty(Cursor $cursor, int &$protection)
    {
        $token = $cursor->getCurrent();

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
