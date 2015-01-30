<?php

namespace RocketShip\Cache;

class Apc
{
    private $caching;

    /**
     *
     * set
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
            if ($this->apc_installed()) {
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
        if ($this->caching == 'yes') {
            if ($this->apc_installed()) {
                apc_delete($key);
            }
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
            if ($this->apc_installed()) {
                return apc_fetch($key);
            }
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
    public final function flush_memory()
    {
        if ($this->caching == 'yes') {
            if ($this->apc_installed()) {
                apc_clear_cache('user');
            }
        }
    }
    
    /**
     *
     * apc_installed
     *
     * Check if APC is installed before calling for it
     *
     * @return  bool        true/false
     * @access  public
     * @final
     *
     */
    private final function apc_installed()
    {
        if (function_exists('apc_store')) {
            return true;
        } else {
            return false;
        }
    }
}
