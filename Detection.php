<?php

require_once 'Property.php';

final class Detection
{
    private $_line   = 0;
    private $_id     = null;
    private $_msg    = null;
    private $_option = 0;

    public function __construct(Token $token, string $msg, int $option)
    {
        $this->_line   = $token->line;
        $this->_id     = $token->id;
        $this->_msg    = $msg;
        $this->_option = $option;
    }

    use Property;
}
