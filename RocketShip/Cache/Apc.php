<?php

namespace RocketShip\Cache;

use RocketShip\CacheAdapter;
use String;
use Number;
use Collection;

class Apc implements CacheAdapter
{
    private $caching;

    public final function connect(){}

    /**
     *
     * Set a variable in the APC server
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
        if ($this->caching == 'yes') {
            if ($this->APCInstalled()) {
                if (apc_exists($key)) {
                    apc_delete($key);
                    apc_store($key, $value, $expire);
                } else {
                    apc_store($key, $value, $expire);
                }
            }
        }
    }

    /**
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
            if ($this->APCInstalled()) {
                apc_delete($key);
            }
        }
    }

    /**
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
            if ($this->APCInstalled()) {
                $value = apc_fetch($key);

                if (is_string($value)) {
                    $value = String::init($value);
                } elseif (is_numeric($value)) {
                    $value = Number::init($value);
                } elseif (is_array($value)) {
                    $value = Collection::init($value);
                }

                return $value;
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
        if ($this->caching == 'yes') {
            if ($this->APCInstalled()) {
                apc_clear_cache('user');
            }
        }
    }
    
    /**
     *
     * Check if APC is installed before calling for it
     *
     * @return  bool        true/false
     * @access  public
     * @final
     *
     */
    private final function APCInstalled()
    {
        if (function_exists('apc_store')) {
            return true;
        } else {
            return false;
        }
    }
}
