<?php

namespace RocketShip;

use RocketShip\Database\Model;

class Authentication extends Model
{
    /**
     *
     * Available system levels, you can always use custom ones
     *
     * Super (200) is the highest number accepted by this system.
     * Higher will result in an invalid level.
     *
     */
    const LEVEL_USER      = 10;
    const LEVEL_WEBMASTER = 20;
    const LEVEL_AUTHOR    = 30;
    const LEVEL_MANAGER   = 80;
    const LEVEL_ADMIN     = 100;
    const LEVEL_SUPER     = 200;

    public function init()
    {
        $this->set('users');
    }

    /**
     *
     * authenticate the given user
     *
     * @param   mixed   string object or php string for username
     * @param   mixed   string object or php string for password
     * @return  bool    success / false + user session creation
     * @access  public
     * @static
     *
     */
    public static function authenticate($user, $password)
    {
        $instance = new self;
        $password = $instance->hashPassword($password);
        $found    = $instance->where(['username' => $user, 'password' => $password])->find();

        if (empty($found)) {
            return false;
        }

        /* Remove password from object */
        $found->password = null;

        $_SESSION['rs_session'] = $found;
        return true;
    }

    /**
     *
     * Check if connected user has the right permission level
     *
     * @param   int     the level to check against
     * @return  bool    yes/no
     * @access  public
     * @static
     *
     */
    public static function hasLevel($level=self::LEVEL_USER)
    {
        /* Block anything higher than super */
        if ($level > self::LEVEL_SUPER) {
            return false;
        }

        if (!empty($_SESSION['rs_session']->level)) {
            return ($_SESSION['rs_session']->level >= $level) ? true : false;
        }

        return false;
    }

    public static function assureLevel($level=self::LEVEL_USER)
    {
        $app = Application::$instance;

        if (Authentication::hasLevel(Authentication::LEVEL_USER)) {
            return true;
        } else {
            include_once $app->root_path . '/app/controllers/Error.php';
            $error = new \ErrorController();
            $error->forbidden();

            $error->view->render('forbidden');
            $app->quit();
        }
    }

    /**
     *
     * Create a new user with the given informations
     *
     * @param   mixed   string object or php string for username
     * @param   mixed   string object or php string for password
     * @param   int     the level to use
     * @param   mixed   collection object or php array for meta data (name, age, etc.)
     * @return  object  Authentication object or null
     * @access  public
     * @static
     *
     */
    public static function create($username, $password, $level=self::LEVEL_USER, $meta=[])
    {
        $instance = new self;

        $found = $instance->where(['username' => $username])->find();

        if (empty($found)) {
            $instance->username = $username;
            $instance->password = $instance->hashPassword($password);
            $instance->level    = $level;
            $instance->meta     = (object)$meta;

            $instance->save();

            return $instance->where(['username' => $username])->find();
        }

        return null;
    }

    private function hashPassword($password)
    {
        $app = Application::$instance;

        /* Try safer encryption method, if it fails, hash it with sha256 */
        if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
            $salt = '$2y$11$' . substr(md5($password . $app->config->general->hash_salt), 0, 22);
            $hash = crypt($password, $salt);
        } else {
            $int_salt = md5($password . $app->config->general->hash_salt);
            $salt     = substr($int_salt, 0, 22);
            $hash     = hash('sha256', $password . $salt);
        }

        return $hash;
    }
}