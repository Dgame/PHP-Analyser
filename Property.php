<?php

trait Property {
    public function __set(string $name, $value)
    {
        throw new Exception('It is not allowed to set ' . $name . ' to ' . $value);
    }

    public function __get(string $name)
    {
        $name = '_' . $name;
        if (!property_exists($this, $name)) {
            throw new Exception('No such attribute ' . $name);
        }

        return $this->{$name};
    }
}
