<?php

namespace RocketShip\Helpers;

use RocketShip\Base;

class Cookie extends Base
{
    private static $instance;

    private $handler;
    private $expiration  = 1209600;
    private $path        = '/';
    private $domain      = null;
    private $httpOnly    = true;
    private $secure      = false;

    public function __construct($expiration=1209600, $path='/', $domain=null, $secure=false, $httpOnly=true)
    {
        if (!empty(self::$instance)) {
            return self::$instance;
        }

        $this->expiration = $expiration;
        $this->path       = $path;
        $this->domain     = $domain;
        $this->secure     = $secure;
        $this->httpOnly   = $httpOnly;
        $this->handler    = array();

        self::$instance = $this;
    }

    /**
     *
     * Singleton retrieval function
     *
     * @return Cookie
     * @access public static
     *
     */
    public final static function getInstance()
    {
        return self::$instance;
    }

    /**
     *
     * Set the value for the given cookie index name
     *
     * @param   string  the index name
     * @param   mixed   value
     * @access  public
     *
     */
    public function set($name, $value)
    {
        $this->handler[$name] = $value;

        $exp  = time() + $this->expiration;
        $json = json_encode($this->handler);
        setcookie('RocketShip', $json, $exp, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }

    /**
     *
     * Get the value for the given index name
     *
     * @param   string  the index name
     * @return  mixed   the value
     * @access  public
     *
     */
    public function get($name)
    {
        if (!empty($this->handler[$name])) {
            return $this->handler[$name];
        }

        return null;
    }

    /**
     *
     * Expire the cookie, like, right now
     *
     * @access  public
     *
     */
    public function expire()
    {
        setcookie('RocketShip', '', time() - 60, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }
}