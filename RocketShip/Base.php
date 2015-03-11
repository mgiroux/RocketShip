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

    /**
     *
     * Transform string, int, float, array or object properties
     * to primitive objects
     *
     * Supports only stdClass instances for objects (json decode returns stdclass instances for example)
     *
     * @param   mixed   target
     * @return  mixed   the matching type object
     * @access  public
     * @static
     *
     */
    public static function toPrimitive($obj)
    {
        if (is_string($obj)) {
            return String::init($obj);
        }

        if (is_numeric($obj)) {
            return Number::init($obj);
        }

        if (is_array($obj)) {
            return Collection::init($obj);
        }

        if (is_object($obj) && $obj instanceof \stdClass) {
            foreach ($obj as $key => $value) {
                if (is_numeric($value)) {
                    $value = new Number($value);
                } elseif (is_string($value)) {
                    $value = new String($value);
                } elseif (is_array($value)) {
                    $value = new Collection($value);
                } elseif ($value instanceof stdClass) {
                    $value = self::toPrimitive($value);
                }
            }

            return $obj;
        }

        return $obj;
    }

    /**
     *
     * Transform a primitive object back to it's php format
     *
     * @param   mixed   object to turn back
     * @return  mixed   the right type for the value
     * @access  public
     * @static
     *
     */
    public static function toRaw($obj)
    {
        if ($obj instanceof String) {
            return $obj->raw();
        }

        if ($obj instanceof Number) {
            return $obj->raw();
        }

        if ($obj instanceof Collection) {
            return $obj->raw();
        }

        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $obj[$key] = self::toRaw($value);
            }

            return $obj;
        }

        if (is_object($obj)) {
            foreach ($obj as $key => $value) {
                $obj->{$key} = self::toRaw($value);
            }

            return $obj;
        }

        return $obj;
    }
}