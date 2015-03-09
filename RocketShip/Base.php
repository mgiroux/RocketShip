<?php

namespace RocketShip;

use String;
use Number;
use Collection;

class Base
{
    /**
     *
     * Application static instance
     * @var \RocketShip\Application
     *
     */
    protected $app;

    public function __construct()
    {
        $this->app = Application::$instance;
    }

    /**
     *
     * Create a new instance of a primitive object
     *
     * @param   mixed   string, float, integer, array(collection)
     * @return  object  the matching object
     * @access  public
     * @final
     *
     */
    final public function primitive($value)
    {
        if (is_string($value)) {
            return new String($value);
        } elseif (is_numeric($value)) {
            return new Number($value);
        } elseif (is_array($value)) {
            return new Collection($value);
        }

        return $value;
    }
}