<?php

require_once 'Property.php';

final class Token
{
    private $_type = 0;
    private $_line = 0;
    private $_id   = '';
    private $_sym  = '';

    public function __construct(int $type, int $line, string $id)
    {
        $this->_type = $type;
        $this->_line = $line;
        $this->_id   = $id;
        $this->_sym  = token_name($type);
    }

    use Property;
}
