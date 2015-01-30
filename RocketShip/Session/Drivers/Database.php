<?php

namespace RocketShip\Session\Drivers;

class Database
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
        $this->model = new Session;
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
        /* Unique key that help prevent session hijacking */ 
        $query = new Query;
        $query->where(array('id' => $id));
        $session = $this->model->find($query);
                
        if (!empty($session)) {
            return json_decode(base64_decode($session->contents));
        } else {
            return '';
        }
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
        /* Unique key that help prevent session hijacking */
        $data = base64_encode(json_encode($data));

        $model              = new Session;
        $model->id          = $id;
        $model->contents    = $data;
        $model->modify_date = time();
        $model->insert();

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
        $this->model->destroyBy(array('id' => $id));
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
        $old = time() - $lifetime;
        $this->model->destroyBy(array('modify_date' => array('&lte' => $old)));
        return true;
    }
}
