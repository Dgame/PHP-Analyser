<?php

require_once 'Property.php';

final class Token
{
    private $_type = 0;
    private $_line = 0;
    private $_id   = '';
    private $_sym  = '';

    public function __construct(int $type, int $line, string $id, string $sym)
    {
        $this->_type = $type;
        $this->_line = $line;
        $this->_id   = $id;
        $this->_sym  = $sym;
    }

    use Property;
}
