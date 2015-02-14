<?php

namespace RocketShip;

class Event extends Base
{
    /* Core Events */
    const CORE_PRE_SETUP       = 'core-pre-setup';
    const CORE_PRE_ROUTES      = 'core-pre-routes';
    const CORE_POST_ROUTES     = 'core-post-route';
    const CORE_PRE_DIRECTIVES  = 'core-post-route';
    const CORE_POST_DIRECTIVES = 'core-post-route';
    const CORE_PRE_BUNDLES     = 'core-pre-bundles';
    const CORE_POST_BUNDLES    = 'core-post-bundles';
    const CORE_PRE_HELPERS     = 'core-pre-helpers';
    const CORE_POST_HELPERS    = 'core-post-helpers';
    const CORE_PRE_SESSION     = 'core-pre-session';
    const CORE_PRE_CONTROLLER  = 'core-pre-controller';
    const CORE_POST_CONTROLLER = 'core-post-controller';
    const CORE_SHUTDOWN        = 'core-shutdown';
    const CORE_API_AUTH        = 'core-api-authenticated';

    /* Database Events */
    const DB_DESTROY_QUERY      = 'db-record-destroy-query';
    const DB_DESTROY_BYID       = 'db-record-destroy-id';
    const DB_DROP_COLLECTION    = 'db-collection-drop';
    const DB_INSERT             = 'db-insert';
    const DB_UPDATE             = 'db-update';
    const DB_GRID_INSERT        = 'db-grid-insert';
    const DB_GRID_DESTROY_BYID  = 'db-grid-destroy-id';
    const DB_GRID_DESTROY_QUERY = 'db-grid-destroy-query';

    /* Upload Events */
    const UPLOAD_DONE    = 'upload-uploaded';
    const UPLOAD_FAILED  = 'upload-failed';
    const UPLOAD_DELETED = 'upload-deleted';
    const UPLOAD_GET     = 'upload-get';

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
                        if (is_callable($event->method)) {
                            $out[] = call_user_func($event->method, $data);
                        } else {
                            $out[] = call_user_func([$event->context, $event->method], $data);
                        }
                    } else {
                        $class = new $event->context;

                        if (is_callable($event->method)) {
                            $out[] = call_user_func($event->method, $data);
                        } else {
                            $out[] = call_user_func([$class, $event->method], $data);
                        }
                    }
                } else {
                    $out[] = call_user_func($event->method, $data);
                }
            }
        }

        self::$triggered_events[] = $name;
        return $out;
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
            self::$registered_events->{$name} = [];
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
