<?php

namespace RocketShip\Controller;

use RocketShip\Base as CaspianBase;
use RocketShip\Configuration;
use RocketShip\Utils\Inflector;

class Base extends CaspianBase
{
    /**
     *
     * Controller's view
     * @var mixed
     *
     */
    public $view;

    /**
     *
     * Controller type
     * @var String
     *
     */
    protected $type = "";

    /**
     *
     * Controller path
     * @var String
     *
     */
    protected $path = "";

    /**
     *
     * Call an other controller and perform an action
     * Controller must reside in the same path as original controller
     * For other directories, use dispatch
     *
     * @param   string  the controller to load
     * @param   string  the method to run
     * @param   mixed   the data to pass to that method
     * @param   bool    Render the view for that controller
     * @throws  \RuntimeException
     * @access  protected
     * @final
     *
     */
    protected final function call($controller, $method, $data=null, $render=false)
    {
        $inflector = new Inflector;

        if (stristr($controller, '.php')) {
            $file = $controller;
            $controller = substr(basename($controller), 0, strlen(basename($controller)) -4);
        } else {
            $file = $this->path . '/' . ucfirst(strtolower($controller)) . '.php';
        }

        if (file_exists($file)) {
            include_once $file;
            $class = $inflector->camelize($controller . '_controller');

            if (class_exists($class)) {
                $instance = new $class;

                if (method_exists($instance, $method)) {
                    call_user_func([$instance, $method], $data);

                    /* Render requested */
                    if ($render) {
                        call_user_func([$instance->view, 'render'], $method);
                        $this->view->setRendered(true);
                    }
                } else {
                    throw new \RuntimeException("Cannot call controller method '{$method}' because the method does not exist. Please check for problems.");
                }
            } else {
                throw new \RuntimeException("Cannot call controller method '{$method}' because the controller class does not exist. Please check for problems.");
            }
        } else {
            throw new \RuntimeException("Cannot call controller method '{$method}' because the controller does not exist. Please check for problems.");
        }
    }

    /**
     *
     * dispatch
     *
     * Dispatch to a bundle's controller the given method and render that method
     *
     * @param   string  bundle name
     * @param   string  controller name
     * @param   string  method name
     * @param   mixed   the data to pass along
     * @access  protectd
     * @final
     *
     */
    protected final function dispatch($bundle, $controller, $method, $data=null)
    {
        $path        = $this->app->root_path . '/bundles/' . $bundle . '/controllers/' . ucfirst(strtolower($controller)) . '.php';
        $bundle_path = $this->app->root_path . '/bundles/' . $bundle;
        $class       = ucfirst(strtolower($controller)) . 'Controller';

        if (file_exists($path)) {
            include_once $path;

            if (class_exists($class)) {
                $instance = new $class($bundle_path);
                $instance->setHelpers();

                if (method_exists($instance, $method)) {
                    call_user_func([$instance, $method], $data);
                    call_user_func(array($instance->view, 'render'), $method);
                    $this->view->setRendered(true);
                } else {
                    throw new \RuntimeException("Dispatch has loaded bundle controller '{$class}', but cannot find method '{$method}'.");
                }
            } else {
                throw new \RuntimeException("Dispatch has loaded bundle controller '{$controller}', but cannot find class '{$class}'.");
            }
        } else {
            throw new \RuntimeException("Dispatch cannot find controller named '{$controller}'.");
        }
    }

    /**
     *
     * dump
     *
     * Dump a variable to the error_log file (hiding the debug from the site/user)
     *
     * @param   string      the variable to dump
     * @access  protected
     *
     */
    protected final function dump($data)
    {
        ob_start();
        print_r($data);
        $dump = ob_get_clean();
        error_log($dump);
    }
}