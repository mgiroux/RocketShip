<?php

class Object
{
    public function __construct($array)
    {
        if (empty($array)) { return; }

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $assoc = array_keys($v) !== range(0, count($v) - 1);

                if (!$assoc) {
                    $this->{$k} = $v;
                } else {
                    $this->{$k} = new Object($v);
                }
            } else {
                $this->{$k} = $v;
            }
        }
    }

    public function addValue($key, $value)
    {
        if (is_array($value)) {
            $assoc = array_keys($value) !== range(0, count($value) - 1);

            if (!$assoc) {
                $this->{$key} = $value;
            } else {
                $this->{$key} = new Object($value);
            }
        } else {
            $this->{$key} = $value;
        }
    }
}
