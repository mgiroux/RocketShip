<?php

namespace RocketShip;

use RocketShip\Base;

class Session extends Base
{
    /* Drive instance */
    private $driver;

    /* Instance of this class */
    private static $instance;
    
    /**
     *
     * __construct
     *
     * Setup the session manager and requested driver
     *
     * @param   string     driver name
     * @access  public
     *
     */
    public function __construct($driver='database')
    {
        parent::__construct();

        $name           = ucfirst(strtolower($driver));
        $class          = '\\RocketShip\\Session\\' . $name;
        $this->driver   = new $class;
        self::$instance = $this;
    }

    /**
     *
     * set
     *
     * Set a value for the given key
     *
     * @param   string  key
     * @param   mixed   value
     * @access  public
     *
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     *
     * getInstance
     *
     * Set a value for the given key
     *
     * @param   string  key
     * @param   mixed   value
     * @access  public
     *
     */    
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     *
     * get
     *
     * Get a session value
     *
     * @param   string  key
     * @return  mixed   value
     * @access  public
     *
     */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }

    /**
     *
     * open
     *
     * The session open method
     *
     * @param    string    the save path
     * @param    string    session id
     * @return   bool      true for success
     * @access   public
     * @final
     *
     */
    public final function open($savepath, $session_id)
    {
        return $this->driver->open($savepath, $session_id);
    }
    
    /**
     *
     * close
     *
     * The session close method
     *
     * @return    bool    true for success
     * @access    public
     * @final
     *
     */
    public final function close()
    {
        return $this->driver->close();
    }
    
    /**
     *
     * read
     *
     * Read the session informations from the database
     *
     * @param     string    session id
     * @return    mixed     the session data
     * @access    public
     * @final
     *
     */
    public final function read($id)
    {
        return $this->driver->read($id);
    }
    
    /**
     *
     * write
     *
     * Write the session to the database
     *
     * @param     string    session id
     * @param     mixed     the session object
     * @return    bool      true for success
     * @access    public
     * @final
     *
     */
    public final function write($id, $data)
    {
        return $this->driver->write($id, $data);
    }
    
    /**
     *
     * destroy
     *
     * Destroy the session from the database
     *
     * @param     string    session id
     * @return    bool      true for success
     * @access    public
     * @final
     *
     */
    public final function destroy($id)
    {
        return $this->driver->destroy($id);
    }
    
    /**
     *
     * garbageCollect
     *
     * Clean old sessions
     *
     * @param     int      the maximum lifetime of a session
     * @return    bool     true for success
     * @access    public
     * @final
     *
     */
    public final function garbageCollect($lifetime)
    {
        return $this->driver->garbageCollect($lifetime);
    }

    /**
     *
     * dump
     *
     * Dump the content of the session or session section
     *
     * @param     string    optional section
     * @return    mixed     the content of the session
     * @access    public
     * @final
     *
     */
    public final function dump($section=null)
    {
        if (!empty($section)) {
            return $_SESSION[$section];
        }

        return $_SESSION;
    }

    /**
     *
     * toJSON
     *
     * Json encode the content of the session or the given section of the session
     *
     * @param     string    the section name (optional)
     * @return    string    json encoded string
     * @access    public
     * @final
     *
     */
    public final function toJSON($section=null)
    {
        if (!empty($section)) {
            return json_encode($_SESSION[$section]);
        }

        return json_encode($_SESSION);
    }

    /**
     *
     * __toString
     *
     * Json encode the content of the session
     *
     * @return    string    json encoded string
     * @access    public
     * @final
     *
     */
    public final function __toString()
    {
        return json_encode($_SESSION);
    }
}
