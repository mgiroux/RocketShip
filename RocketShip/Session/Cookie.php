<?php

namespace RocketShip\Session;

use RocketShip\Base;

class Cookie extends Base
{
    protected $_algo = MCRYPT_RIJNDAEL_128;
    protected $_key;
    protected $_auth;
    protected $_path;
    protected $_name;
    protected $_ivSize;
    protected $_keyName;

    /**
     *
     * The session open method
     *
     * @param    string    the save path
     * @param    string    session id
     * @param    string    the session's name
     * @return   bool      true for success
     * @access   public
     * @final
     *
     */
    public final function open($save_path, $session_id, $session_name='RocketShip')
    {
        $this->_path    = $save_path . '/';
        $this->_name    = $session_name;
        $this->_keyName = 'skey_' . $session_name;
        $this->_ivSize  = mcrypt_get_iv_size($this->_algo, MCRYPT_MODE_CBC);

        if (empty($_COOKIE[$this->_keyName]) || strpos($_COOKIE[$this->_keyName], ':') === false) {
            $keyLength    = mcrypt_get_key_size($this->_algo, MCRYPT_MODE_CBC);
            $this->_key   = self::_randomKey($keyLength);
            $this->_auth  = self::_randomKey(32);
            $cookie_param = session_get_cookie_params();

            setcookie(
                $this->_keyName,
                base64_encode($this->_key) . ':' . base64_encode($this->_auth),
                ($cookie_param['lifetime'] > 0) ? time() + $cookie_param['lifetime'] : 0,
                $cookie_param['path'],
                $cookie_param['domain'],
                $cookie_param['secure'],
                $cookie_param['httponly']
            );
        } else {
            list ($this->_key, $this->_auth) = explode(':', $_COOKIE[$this->_keyName]);
            $this->_key  = base64_decode($this->_key);
            $this->_auth = base64_decode($this->_auth);
        }

        return true;
    }

    /**
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
        $sess_file = $this->_path . $this->_name . "_$id";
        if (!file_exists($sess_file)) {
            return false;
        }
        $data = file_get_contents($sess_file);
        list($hmac, $iv, $encrypted) = explode(':', $data);
        $iv        = base64_decode($iv);
        $encrypted = base64_decode($encrypted);
        $newHmac   = hash_hmac('sha256', $iv . $this->_algo . $encrypted, $this->_auth);
        if ($hmac !== $newHmac) {
            return false;
        }
        $decrypt = mcrypt_decrypt(
            $this->_algo,
            $this->_key,
            $encrypted,
            MCRYPT_MODE_CBC,
            $iv
        );

        return rtrim($decrypt, "\0");
    }

    /**
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
        $sess_file = $this->_path . $this->_name . "_$id";
        $iv        = mcrypt_create_iv($this->_ivSize, MCRYPT_DEV_URANDOM);
        $encrypted = mcrypt_encrypt($this->_algo, $this->_key, $data, MCRYPT_MODE_CBC, $iv);
        $hmac      = hash_hmac('sha256', $iv . $this->_algo . $encrypted, $this->_auth);
        $bytes     = @file_put_contents($sess_file, $hmac . ':' . base64_encode($iv) . ':' . base64_encode($encrypted));

        return ($bytes !== false);
    }

    /**
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
        $sess_file = $this->_path . $this->_name . "_$id";
        setcookie($this->_keyName, '', time() - 3600);

        return (@unlink($sess_file));
    }

    /**
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
        foreach (glob($this->_path . $this->_name . '_*') as $filename) {
            if (filemtime($filename) + $lifetime < time()) {
                @unlink($filename);
            }
        }

        return true;
    }

    /**
     *
     * Generate a random key using openssl, fallback to mcrypt_create_iv
     *
     * @param   int         length of the key
     * @return  string      a random key
     * @access  protected
     * @throws  Exception   failure to use openssl and or mcrypt
     */
    protected function _randomKey($length = 32)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($length, $strong);
            if ($strong === true) {
                return $rnd;
            }
        }

        if (function_exists('mcrypt_create_iv')) {
            return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        } else {
            throw new Exception('OpenSSL and Mcrypt are not installed, cannot proceed');
        }
    }
}
