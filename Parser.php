<?php

error_reporting(E_ALL);

require_once 'basic_type_hint.php';
require_once 'Tokenizer.php';
require_once 'Cursor.php';
require_once 'Scopes.php';
require_once 'Detector.php';
require_once 'Analyser/Analyser.php';

final class Parser
{
    private static $Assignments = [
        T_AND_EQUAL    => true,
        T_OR_EQUAL     => true,
        T_CONCAT_EQUAL => true,
        T_DOUBLE_ARROW => true,
        T_MUL_EQUAL    => true,
        T_DIV_EQUAL    => true,
        T_MOD_EQUAL    => true,
        T_PLUS_EQUAL   => true,
        T_MINUS_EQUAL  => true,
        T_XOR_EQUAL    => true,
        T_EQUAL        => true,
    ];

    public static function IsAssignment(Token $token)
    {
        return array_key_exists($token->type, self::$Assignments);
    }

    private $_detector = null;

    public function __construct(int $analyse_options)
    {
        $this->_detector = new Detector($analyse_options);
    }

    public function parse(string $filename, $options = 0)
    {
        $scopes    = new Scopes();
        $tokenizer = new Tokenizer($filename);
        $cursor    = new Cursor($tokenizer->getTokens());

        do {
            $moved = false;
            $token = $cursor->getCurrent();

            if ($token->type == T_OPEN_CURLY) {
                $scopes->open();
            } elseif ($token->type == T_CLOSE_CURLY) {
                $scopes->close();
            }

            // print "\t" . '<pre>' . $token->id . ':' . $token->sym;
            $analyser = $this->_getAnalyserFor($token, (int) $options);
            if ($analyser) {
                $moved = $analyser->analyse($scopes, $cursor);
            }

            if (!$moved) {
                $cursor->next();
            }
        } while ($cursor->isValid());

        // print '<pre>';
        // print_r($scopes->getAllScopes());

        $this->_detector->detectIn($scopes);
    }

    private function _getAnalyserFor(Token $token, int $options)
    {
        if (self::IsAssignment($token)) {
            return new AssignmentAnalyser($this->_detector, $options);
        }

        switch ($token->type) {
            case T_CLASS:
                return new ClassAnalyser($this->_detector, $options);
            case T_FUNCTION:
                return new FunctionAnalyser($this->_detector, $options);
            case T_VARIABLE:
                return new VariableAnalyser($this->_detector, $options);
            case T_SELF:
            case T_PARENT:
            case T_STATIC:
                return new StaticAnalyser($this->_detector, $options);
            case T_WHILE:
            case T_DO:
            case T_FOR:
            case T_FOREACH:
                return new LoopAnalyser($this->_detector, $options);
        }

        return null;
    }
}
