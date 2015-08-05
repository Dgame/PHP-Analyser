<?php

abstract class Foo
{
    private static $Assignments = [
        T_AND_EQUAL    => true,
        T_AND_EQUAL    => true,
        T_DIV_EQUAL    => true,
        T_DOUBLE_ARROW => true,
        T_DOUBLE_ARROW => true,
        T_MOD_EQUAL    => true,
        T_MUL_EQUAL    => true,
        T_PLUS_EQUAL   => true,
        T_PLUS_EQUAL   => true
    ];

    private static $Foo = null;
    public static $Bar = null;

    public static function IsAssignment(Token $token)
    {
        return array_key_exists($token->type, self::$Assignments) || $token->id == '=';
    }

    public $abc = null;

    public function __constructor(Token $token)
    {
        $this->abc = 1;
        $this->xyz = 0;

        $foo = 'Bar';
        $_ = $this->{$foo};
    }

    abstract public function foo($test);
}

function foo($a, $b)
{
    $xyz = null;
    $abc = $xyz->foo();

    $k = 0;

    $a = $d;
    $c;
}

$cursor = 0;
for ($i = 0; $i < 4; $i++) {
    $cursor = $i;
}

$arr = [1, 2, 3];
foreach ($arr as &$item) {
    $tmp = $item;
}
