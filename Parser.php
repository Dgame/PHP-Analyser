<?php

error_reporting(E_ALL);

require_once 'basic_type_hint.php';
require_once 'Token.php';
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
        $scopes = new Scopes();

        $tokens = $this->_processTokens($filename);
        $cursor = new Cursor($tokens);

        do {
            $moved = false;
            $token = $cursor->getCurrentToken();

            if ($token->id == '{') {
                $scopes->open();
            } elseif ($token->id == '}') {
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

    private function _processTokens(string $filename)
    {
        $content      = file_get_contents($filename);
        $input_tokens = token_get_all($content);

        $output_tokens = [];
        foreach ($input_tokens as $token_data) {
            $token = $this->_createTokenObject($token_data);
            if ($token->type != T_WHITESPACE) {
                $output_tokens[] = $token;
            }
        }

        return $output_tokens;
    }

    private function _createTokenObject($token_data)
    {
        if (is_array($token_data)) {
            list($type, $id, $line) = $token_data;

            return new Token($type, $line, $id);
        }

        return new Token(0, 0, $token_data);
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
            case T_STRING:
                if ($token->id != 'self' && $token->id != 'parent') {
                    return null;
                }
            case T_STATIC:
                return new StaticAnalyser($this->_detector, $options);
        }

        return null;
    }
}
