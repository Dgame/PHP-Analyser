<?php

require_once 'Property.php';

final class Variable
{
    private $_id   = null;
    private $_line = 0;

    public $location   = null;
    public $usage      = 0;
    public $protection = 0;
    public $state      = 0;

    public $defined     = false;
    public $initialized = false;
    public $assignment  = false;
    public $parameter   = false;
    public $property    = false;

    public function __construct(string $id, int $line)
    {
        $this->_id   = $id;
        $this->_line = $line;
    }

    public function __toString()
    {
        $kind = 'Variable';
        if ($this->property) {
            $kind = 'Property';
        } elseif ($this->parameter) {
            $kind = 'Parameter';
        }

        if ($this->property) {
            return $kind . ' ' . $this->name;
        }

        return $kind . ' ' . $this->id;
    }

    public function __get(string $name)
    {
        if ($name === 'name') {
            return substr($this->_id, 1);
        }

        if ($name === 'id' || $name === 'line') {
            $name = '_' . $name;

            return $this->{$name};
        }

        throw new Exception('No such attribute ' . $name);
    }

    use Property;
}
