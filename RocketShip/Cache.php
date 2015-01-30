<?php

namespace RocketShip;

define ('CACHE_NEVER', 0);
define ('CACHE_TEN', 600);
define ('CACHE_HALFHOUR', 1800);
define ('CACHE_HOUR', 3600);
define ('CACHE_DAY', 86400);
define ('CACHE_WEEK', 604800);
define ('CACHE_MONTH', 2592000);

class Cache extends Base
{
    /**
     *
     * Instance of the cache class
     *
     * @var \RocketShip\Cache
     *
     */
    public static $_instance;

    protected $config;
    private $driver;
    private $caching;
    private $key;
    
    private final function setup()
    {
        $this->caching = $this->app->config->performance->cache->use;
        $this->key     = $this->app->config->performance->cache->application_key;

        if ($this->caching == 'yes') {
            $driver         = strtolower($this->app->config->performance->cache->driver);
            $file           = dirname(__FILE__) . '/Cache/' . ucfirst($driver) . '.php';
            $class          = ucfirst($driver);

            //include_once $file;
            $class = "\\RocketShip\\Cache\\" . $class;
            $obj   = new $class;

            if (method_exists($obj, 'connect')) {
                /* Connect to caching server */
                $obj->connect();
            }
            
            $this->driver = $obj;
        }
    }
    
    /**
     *
     * getInstance
     *
     * Get the singleton instance of the cache object
     *
     * @return  object  cache object
     * @access  public
     * @final
     * @static
     *
     */
    public static final function getInstance()
    {   
        if (empty(self::$_instance)) {
            self::$_instance = new Cache;
            self::$_instance->setup();
            return self::$_instance;    
        } else {
            return self::$_instance;
        }
    }
    
    /**
     *
     * get
     *
     * Get element(s) from server
     *
     * @param   mixed     string/array (key or array of keys)
     * @return  object    result object
     * @access  public
     * @final
     *
     */
    public final function get($key)
    {
        if ($this->caching == 'yes') {
            return $this->driver->get($this->key . '_' . $key);
        } else {
            return null;
        }
    }
    
    /**
     *
     * set
     *
     * Set a variable in the cache server
     *
     * @param   string  key
     * @param   mixed   array/object
     * @param   int     time to live (ttl) in seconds or timestamp (default: 10 minutes)
     * @access  public
     * @final
     *
     */
    public final function set($key, $val, $ttl=CACHE_DAY)
    {
        if ($this->caching == 'yes') {
            $this->driver->set($this->key . '_' . $key, $val, $ttl);
        }
    }
    
    /**
     *
     * _delete
     *
     * Delete given element from the cache
     *
     * @param   string    key
     * @access  public
     * @final
     *
     */
    public final function delete($key)
    {
        if ($this->caching == 'yes') {
            $this->driver->delete($this->key . '_' . $key);
        }
    }
    
    /**
     *
     * flush_memory
     *
     * Flush all memory blocks
     *
     * @return  void
     * @access  public
     * @final
     *
     */
    public final function flush()
    {
        if ($this->caching == 'yes') {
            $this->driver->flush_memory();
        }
    }
}
