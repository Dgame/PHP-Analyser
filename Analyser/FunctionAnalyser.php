<?php

require_once 'Analyser.php';

class FunctionAnalyser extends Analyser
{
    private static $MisspelledMagicNames = [
        'construct'     => '__construct',
        '_construct'    => '__construct',
        'constructor'   => '__construct',
        '_constructor'  => '__construct',
        'destruct'      => '__destruct',
        '_destruct'     => '__destruct',
        'destructor'    => '__destruct',
        '_destructor'   => '__destruct',
    ];

    private static $MagicNames = [
        '__construct'  => true,
        '__destruct'   => true,
        '__call'       => true,
        '__callStatic' => true,
        '__get'        => true,
        '__set'        => true,
        '__isset'      => true,
        '__unset'      => true,
        '__sleep'      => true,
        '__wakeup'     => true,
        '__toString'   => true,
        '__invoke'     => true,
        '__set_state'  => true,
        '__clone'      => true,
        '__debugInfo'  => true,
    ];

    public function __construct(Detector $detector, int $options)
    {
        parent::__construct($detector, $options);
    }

    public function analyse(Scopes $scopes, Cursor $cursor)
    {
        $token = $cursor->getCurrentToken();
        assert($token->type == T_FUNCTION);

        $scope = new Scope($token->line, T_FUNCTION, $scopes->getCurrentScope());
        $scopes->pushScope($scope);

        $cursor->next(); // jump over T_FUNCTION
        $token = $cursor->getCurrentToken(); // store the function name
        $this->_analyseFunctionName($token);

        $cursor->skipUntil('(');
        $tok = $cursor->getCurrentToken();
        do {
            if ($tok->type == T_VARIABLE) {
                if ($this->_options & (Options::Verbose | Options::Debug)) {
                    print '<pre>[FA] ' . $token->line . ' : Found parameter ' . $tok->id;
                }

                $var              = new Variable($tok->id, $tok->line);
                $var->location    = 'function:' . $token->id;
                $var->parameter   = true;
                $var->usage       = 0;
                $var->initialized = true; // Default auf true
                $var->defined     = true; // Default auf true

                $scope->addVariable($var);
            }

            $cursor->next();
            $tok = $cursor->getCurrentToken();
        } while ($cursor->isValid() && !($tok->id == '{' || $tok->id == ';'));

        if ($tok->id == ';') {
            foreach ($scope->variables as $var) {
                $var->state = T_ABSTRACT;
            }

            $scopes->popScope();
            // print '<pre> ---- ' . token_name($scopes->getCurrentScope()->token);
        }

        return true;
    }

    private function _analyseFunctionName(Token $token)
    {
        assert($token->type == T_STRING);

        if (array_key_exists($token->id, self::$MisspelledMagicNames)) {
            $msg = 'Found "' . $token->id . '", did you mean "' . self::$MagicNames[$token->id] . '"?';
            $this->_detector->addDetection($token, $msg, Detect::PossibleMisspelling);
        } else if (substr($token->id, 0, 2) == '__' && !array_key_exists($token->id, self::$MagicNames)) {
            $last_percent = 0;
            $name = '';
            foreach (self::$MagicNames as $magicName => $_) {
                similar_text($token->id, $magicName, $percent);
                if ($last_percent < $percent) {
                    $last_percent = $percent;
                    $name = $magicName;
                }
            }

            if ($name != null) {
                $msg = 'Found "' . $token->id . '", did you mean "' . $name . '"?';
                $this->_detector->addDetection($token, $msg, Detect::PossibleMisspelling);
            }
        }
    }
}
