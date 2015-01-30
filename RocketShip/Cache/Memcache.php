<?php

namespace RocketShip\Cache;

use \RocketShip\Configuration;

class Memcache
{
    protected $link;

    /**
     *
     * Construct
     *
     * Connect to the memcache daemon if memcache support is turned on
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
        if (class_exists('\\Memcache')) {
            $this->link = new \Memcache;
            $this->link->connect(Configuration::get('configuration', 'performance.cache.host'), Configuration::get('configuration', 'performance.cache.port'));
            $this->link->setCompressThreshold(2000, 0.2);
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
            $this->delete($key);
            $this->link->add($key, $value, MEMCACHE_COMPRESSED, $expire); 
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
            $this->link->delete($key, 0);
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
            $arr = $this->link->get($key);
            
            if (empty($arr)) {
                return null;
            } else {
                return $arr;
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
        if (!empty($this->link)) {
            $this->link->flush();
        }
    }
}
