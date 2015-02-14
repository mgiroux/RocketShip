<?php

namespace RocketShip;

class Filter extends Base
{
    /* Regitered filters object */
    private static $registered_filters;

    /* Triggered filters history */
    private static $triggered_filters = [];

    /**
     *
     * Construct
     *
     */
    public function __construct()
    {
        parent::__construct();

        if (empty(self::$registered_filters)) {
            self::$registered_filters = new \stdClass;
        }
    }

    /**
     *
     * Trigger a filter
     *
     * @param     string    filter name
     * @param     mixed     the data to pass to the callback
     * @param     string    type of callback (event or filter)
     * @return    array     callback returned values
     * @access    public
     *
     */
    public function trigger($name, $data)
    {
        $name = str_replace(" ", "-", strtolower($name));
        $out  = [];

        if (!empty(self::$registered_filters->{$name})) {
            foreach (self::$registered_filters->{$name} as $filter) {
                if (!empty($filter->context)) {
                    if (is_object($filter->context)) {
                        if (is_callable($filter->method)) {
                            $data = call_user_func($filter->method, $data);
                        } else {
                            $data = call_user_func([$filter->context, $filter->method], $data);
                        }
                    } else {
                        $class = new $filter->context;

                        if (is_callable($filter->method)) {
                            $data = call_user_func($filter->method, $data);
                        } else {
                            $data = call_user_func([$class, $filter->method], $data);
                        }
                    }
                } else {
                    $data = call_user_func($filter->method, $data);
                }
            }
        }

        self::$triggered_filters[] = $name;
        return $data;
    }

    /**
     *
     * Register for a filter
     *
     * @param     string   filter name
     * @param     mixed    the context in which for the callback (ether classname string or object)
     * @param     mixed    callback name or closure
     * @return    void
     * @access    public
     *
     */
    public function register($name, $context=null, $method)
    {
        $name = str_replace(" ", "_", strtolower($name));

        if (empty(self::$registered_filters)) {
            self::$registered_filters = new \stdClass;
        }

        if (empty(self::$registered_filters->{$name})) {
            self::$registered_filters->{$name} = [];
        }

        $filter_object          = new \stdClass;
        $filter_object->context = $context;
        $filter_object->method  = $method;

        array_push(self::$registered_filters->{$name}, $filter_object);
    }

    /**
     *
     * Check if a given event has been triggered already
     *
     * @param   string  event name
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isTriggered($name)
    {
        if (in_array($name, self::$triggered_filters)) {
            return true;
        } else {
            return false;
        }
    }
}
