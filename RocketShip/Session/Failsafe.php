<?php

namespace RocketShip\Session;

use RocketShip\Base;

class Failsafe extends Base
{
    /* Model instance */
    private $model;

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
        return true;
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
        return true;
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
        return $_SESSION;
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
        return true;
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
        $_SESSION = null;
        return true;
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
        return true;
    }
}
