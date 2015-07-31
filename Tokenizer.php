<?php

require_once 'Token.php';

define('T_DOT', 1);
define('T_COMMA', 2);
define('T_COLON', 3);
define('T_SEMICOLON', 4);
define('T_QUERY', 5); // ?
define('T_NOT', 6);
define('T_EQUAL', 7);

define('T_PLUS', 8);
define('T_MINUS', 9);
define('T_MUL', 10);
define('T_DIV', 11);
define('T_MOD', 12);

define('T_OPEN_CURLY', 13); // {
define('T_CLOSE_CURLY', 14); // }
define('T_OPEN_BRACKET', 15); // [
define('T_CLOSE_BRACKET', 16); // ]
define('T_OPEN_PAREN', 17); // (
define('T_CLOSE_PAREN', 18); // )

define('T_SELF', 19); // self
define('T_PARENT', 20); // parent
define('T_NULL', 21); // null

define('T_AND', 22); // &
define('T_OR', 23); // |
define('T_XOR', 24); // ^

define('UNKNOWN_TOKEN_TYPE', 0);
define('UNKNOWN_TOKEN', token_name(UNKNOWN_TOKEN_TYPE));

function get_token_name(int $value)
{
    $constants = get_defined_constants(true)['user'];
    $name      = array_search($value, $constants);
    if (false !== $name) {
        return $name;
    }

    return UNKNOWN_TOKEN;
}

final class Tokenizer
{
    private static $TypedTokens = [
        '.' => T_DOT,
        ',' => T_COMMA,
        ':' => T_COLON,
        ';' => T_SEMICOLON,
        '?' => T_QUERY,
        '!' => T_NOT,
        '=' => T_EQUAL,
        '+' => T_PLUS,
        '-' => T_MINUS,
        '*' => T_MUL,
        '/' => T_DIV,
        '%' => T_MOD,
        '{' => T_OPEN_CURLY,
        '}' => T_CLOSE_CURLY,
        '[' => T_OPEN_BRACKET,
        ']' => T_CLOSE_BRACKET,
        '(' => T_OPEN_PAREN,
        ')' => T_CLOSE_PAREN,
        '&' => T_AND,
        '|' => T_OR,
        '^' => T_XOR,
    ];

    private static $SpecialTypedTokens = [
        'self'   => T_SELF,
        'parent' => T_PARENT,
        'null'   => T_NULL,
    ];

    private static $IgnoredTokens = [
        T_WHITESPACE => true,
    ];

    private $_tokens = [];

    public function __construct(string $filename)
    {
        if (!file_exists($filename)) {
            throw new Exception('File does not exists: ' . $filename);
        }
        
        $content = file_get_contents($filename);
        $tokens  = token_get_all($content);

        $this->_processTokens($tokens);
    }

    private function _processTokens(array $tokens)
    {
        $line = 0;
        foreach ($tokens as $raw_token) {
            $token = $this->_createTokenObject($raw_token, $line);

            if ($token->line != 0) {
                $line = $token->line;
            }

            if (!array_key_exists($token->type, self::$IgnoredTokens)) {
                $this->_tokens[] = $token;
            }
        }
    }

    private function _createTokenObject($token, int $line)
    {
        if (is_array($token)) {
            list($type, $id, $line) = $token;

            if (array_key_exists($id, self::$SpecialTypedTokens)) {
                $type = self::$SpecialTypedTokens[$id];
            }

            return new Token($type, $line, $id, token_name($type));
        }

        if (array_key_exists($token, self::$TypedTokens)) {
            $type = self::$TypedTokens[$token];

            return new Token($type, $line, $token, get_token_name($type));
        }

        return new Token(UNKNOWN_TOKEN_TYPE, $line, $token, UNKNOWN_TOKEN);
    }

    public function getTokens()
    {
        return $this->_tokens;
    }
}
