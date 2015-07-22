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
            $token = $cursor->getCurrentToken();

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
        if (Analyser::IsAssignment($token)) {
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
        }

        return null;
    }
}
