<?php

namespace RocketShip\Session;

use RocketShip\Base;
use RocketShip\Database\Collection;

class Database extends Base
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
        $this->model = new Collection('sessions');
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
        $session = $this->model->where(['id' => $id])->find();
                
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

        $session = $this->model->where(['id' => $id])->find();
      
        if (!empty($session)) {
            $model              = new Collection('sessions');
            $model->_id         = $session->_id;
            $model->id          = $id;
            $model->contents    = $data;
            $model->modify_date = time();
            $model->save();
        } else {
            $model              = new Collection('sessions');
            $model->id          = $id;
            $model->contents    = $data;
            $model->modify_date = time();
            $model->save();
        }

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
        $this->model->destroyById($id);
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

        $this->model->where(['modify_date' => ['&lte' => $old]])->destroy();
        return true;
    }
}
