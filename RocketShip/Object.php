<?php

class Object
{
    public function __construct($array)
    {
        if (empty($array)) { return; }

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $this->{$k} = new Object($v);
            } else {
                $this->{$k} = $v;
            }
        }
    }

    public function addValue($key, $value)
    {
        if (is_array($value)) {
            $this->{$key} = new Object($value);
        } else {
            $this->{$key} = $value;
        }
    }
}
