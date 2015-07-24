<?php

require_once 'Property.php';

final class Variable
{
    private static $Exceptions = [
        '$_SERVER'          => true,
        '$_REQUEST'         => true,
        '$_POST'            => true,
        '$_GET'             => true,
        '$_FILES'           => true,
        '$_ENV'             => true,
        '$_COOKIE'          => true,
        '$_SESSION'         => true,
        '$GLOBALS'          => true,
        '$HTTP_ENV_VARS'    => true,
        '$HTTP_POST_VARS'   => true,
        '$HTTP_GET_VARS'    => true,
        '$HTTP_COOKIE_VARS' => true,
        '$HTTP_SERVER_VARS' => true,
        '$HTTP_POST_FILES'  => true,
        '$this'             => true,
        '$argv'             => true,
        '$argc'             => true,
        '$_'                => true, // for valid unused variables
    ];

    public static function Approve(string $id)
    {
        return !array_key_exists($id, self::$Exceptions);
    }

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
    public $reference   = false;

    public function __construct(string $id, int $line)
    {
        $this->_id   = $id;
        $this->_line = $line;
    }

    public function __toString()
    {
        $state = $this->state == T_STATIC ? 'static ' : null;

        $kind = 'Variable ';
        if ($this->property) {
            $kind = 'Property ';
        } elseif ($this->parameter) {
            $kind = 'Parameter ';
        }

        if ($this->property) {
            return $state . $kind . $this->name;
        }

        return $state . $kind . $this->id;
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
