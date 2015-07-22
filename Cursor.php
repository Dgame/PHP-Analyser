<?php

final class Cursor
{
    private $_tokens    = [];
    private $_saved_pos = [];

    private $_tok_count = 0;
    private $_cur_pos   = 0;

    public function __construct(array $tokens)
    {
        $this->_tokens    = $tokens;
        $this->_tok_count = count($tokens);
    }

    public function getTokenCount()
    {
        return $this->_tok_count;
    }

    public function isValid()
    {
        return $this->_cur_pos < $this->_tok_count;
    }

    public function getCurrent()
    {
        if ($this->isValid()) {
            return $this->_tokens[$this->_cur_pos];
        }

        return null;
    }

    public function next()
    {
        if ($this->isValid()) {
            $this->_cur_pos++;
        }
    }

    public function lookAhead()
    {
        $this->next();
        $next_token = $this->getCurrent();
        $this->previous();

        return $next_token;
    }

    public function previous()
    {
        if ($this->_cur_pos > 0) {
            $this->_cur_pos--;
        }
    }

    public function lookBehind()
    {
        $this->previous();
        $prev_token = $this->getCurrent();
        $this->next();

        return $prev_token;
    }

    public function pushPosition()
    {
        array_push($this->_saved_pos, $this->_cur_pos);
        // print '<pre>Save position: ' . $this->_cur_pos;
    }

    public function popPosition()
    {
        $this->_cur_pos = array_pop($this->_saved_pos);
        // print '<pre>Load position: ' . $this->_cur_pos;
    }

    public function __call(string $name, array $args)
    {
        if ($name == 'skipUntil') {
            $param = array_pop($args);
            if (is_int($param)) {
                return $this->skipUntilType((int) $param);
            }

            return $this->skipUntilID($param);
        }

        throw new Exception('No such method: ' . $name);
    }

    public function skipUntilID(string $id)
    {
        for (; $this->isValid() && $this->getCurrent()->id != $id; $this->next()) {
        }
    }

    public function skipUntilType(int $type)
    {
        for (; $this->isValid() && $this->getCurrent()->type != $type; $this->next()) {
        }
    }
}
