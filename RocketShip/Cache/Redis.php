<?php

namespace RocketShip\Cache;

use RocketShip\CacheAdapter;
use RocketShip\Configuration;

class Redis implements CacheAdapter
{
    protected $link;

    /**
     *
     * Construct
     *
     * Connect to the redis daemon if redis support is turned on
     *
     * @return  void
     * @access  public
     * @static
     * @final
     *
     */
    public final function connect()
    {
        /* Look for availability of the php extension */
        if (class_exists('\\Redis')) {
            $this->link = new \Redis;
            $this->link->connect(Configuration::get('configuration', 'performance.cache.host'), Configuration::get('configuration', 'performance.cache.port'));
            $this->link->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            $this->link->select(Configuration::get('configuration', 'performance.cache.dbindex'));
        }
    }

    /**
     *
     * set
     *
     * Set a variable in the memcache server
     *
     * @param   string  key
     * @param   mixed   array/object
     * @param   int     time to live (ttl) in seconds or timestamp (default: 10 minutes)
     * @access  public
     * @final
     *
     */
    public final function set($key, $value, $expire=CACHE_TEN)
    {
        if (!empty($this->link)) {
            /* Delete the item if it exists */
            $ret = $this->link->set($key, json_encode($value), $expire);
        }
    }

    /**
     *
     * delete
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
        if (!empty($this->link)) {
            $this->link->delete($key);
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
        if (!empty($this->link)) {
            $arr = json_decode($this->link->get($key));

            if (empty($arr)) {
                return null;
            } else {
                return $arr;
            }
        }
    }

    /**
     *
     * Flush all memory blocks
     *
     * @return  void
     * @access  public
     * @final
     *
     */
    public final function flushMemory()
    {
        if (!empty($this->link)) {
            $this->link->flushDB();
        }
    }
}
