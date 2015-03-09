<?php

namespace RocketShip;

use RocketShip\Database\Collection;
use String;

abstract class Bundle extends Base
{
    private $bundle_path;

    abstract public function init();
    abstract public function install();

    private static $dependency_check = [];

    /**
     *
     * Set the bundles path for later use
     *
     * @param   string  the path
     * @access  public
     *
     */
    public function setBundlePath($path)
    {
        $this->bundle_path = String::init($path);
    }

    /**
     *
     * Return the bundle's path
     *
     * @return  string  the bundle path
     * @access  public
     *
     */
    public function getBundlePath()
    {
        return $this->bundle_path;
    }
    
    /**
     *
     * Check if bundle is installed, if not install it
     *
     * @access  public
     *
     */
    public function verifyInstall()
    {
        $bundle_model = new Collection('bundles');
        $bundle_class = get_class($this);

        if ($bundle_model->count(['class_name' => $bundle_class]) == 0) {
            $this->install();

            $bundle_model->class_name = $bundle_class;
            $bundle_model->save();
        }
    }

    /**
     *
     * Check if the given bundle exists / is loaded
     *
     * @param   string      the bundle name (without the "Bundle" part)
     * @access  public
     *
     */
    public final static function dependency($class)
    {
        $app = Application::$instance;

        if ($app->events->isTriggered('post-bundles')) {
            /* Boot already done, just call it directly */
            self::$dependency_check[] = $class;
            self::dependencies();
        } else {
            /* We are not ready to call this yet, register for event */
            self::$dependency_check[] = $class;
            $app->events->register('post-bundles', null, 'RocketShip\Bundle::dependencies');
        }
    }

    /**
     *
     * Check if all the requested dependency check up is available, kill the page if not
     *
     * @return  bool    true for success, dies if not available
     * @access  public
     * @final
     * @static
     *
     */
    static public final function dependencies()
    {
        foreach (self::$dependency_check as $class) {
            if (class_exists($class) || class_exists($class . 'Bundle')) {
                return true;
            } else {
                throw new \RuntimeException("Dependency failure: '{$class}' does not exist or is not loaded. Please verify your settings and try again.");
            }
        }
    }
}
