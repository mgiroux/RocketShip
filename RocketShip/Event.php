<?php

namespace RocketShip;

class Event extends Base
{
    /* Regitered events object */
    private static $registered_events;

    /* Triggered events history */
    private static $triggered_events = [];

    /**
     *
     * Construct
     *
     */
    public function __construct()
    {
        parent::__construct();

        if (empty(self::$registered_events)) {
            self::$registered_events = new \stdClass;
        }
    }

    /**
     *
     * Trigger an event
     *
     * @param     string    event name
     * @param     mixed     the data to pass to the callback
     * @param     string    type of callback (event or filter)
     * @return    array     callback returned values
     * @access    public
     *
     */
    public function trigger($name, $data, $type='event')
    {
        $name = str_replace(" ", "-", strtolower($name));
        $out  = [];

        if (!empty(self::$registered_events->{$name})) {
            foreach (self::$registered_events->{$name} as $event) {
                if (!empty($event->context)) {
                    if (is_object($event->context)) {
                        if ($type == 'event') {
                            if (is_callable($event->method)) {
                                $out[] = call_user_func($event->method, $data);
                            } else {
                                $out[] = call_user_func(array($event->context, $event->method), $data);
                            }
                        } else {
                            if (is_callable($event->method)) {
                                $data = call_user_func($event->method, $data);
                            } else {
                                $data = call_user_func(array($event->context, $event->method), $data);
                            }
                        }
                    } else {
                        $class = new $event->context;

                        if ($type == 'event') {
                            if (is_callable($event->method)) {
                                $out[] = call_user_func($event->method, $data);
                            } else {
                                $out[] = call_user_func(array($class, $event->method), $data);
                            }
                        } else {
                            if (is_callable($event->method)) {
                                $data = call_user_func($event->method, $data);
                            } else {
                                $data = call_user_func(array($class, $event->method), $data);
                            }
                        }
                    }
                } else {
                    if ($type == 'event') {
                        $out[] = call_user_func($event->method, $data);
                    } else {
                        $data = call_user_func($event->method, $data);
                    }
                }
            }
        }

        self::$triggered_events[] = $name;

        if ($type == 'event') {
            return $out;
        } else {
            return $data;
        }
    }

    /**
     *
     * Register for an event
     *
     * @param     string   event name
     * @param     mixed    the context in which for the callback (ether classname string or object)
     * @param     mixed    callback name or closure
     * @return    void
     * @access    public
     *
     */
    public function register($name, $context=null, $method)
    {
        $name = str_replace(" ", "_", strtolower($name));

        if (empty(self::$registered_events)) {
            self::$registered_events = new \stdClass;
        }

        if (empty(self::$registered_events->{$name})) {
            self::$registered_events->{$name} = array();
        }

        $event_object          = new \stdClass;
        $event_object->context = $context;
        $event_object->method  = $method;

        array_push(self::$registered_events->{$name}, $event_object);
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
        if (in_array($name, self::$triggered_events)) {
            return true;
        } else {
            return false;
        }
    }
}
