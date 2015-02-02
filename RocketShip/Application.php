<?php

namespace RocketShip;

use RocketShip\Database\Collection;
use RocketShip\Utils\IO;
use RocketShip\Utils\Request;
use RocketShip\Security\Input;
use RocketShip\Session;
use RocketShip\Directives;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class Application
{
    const VERSION    = '1.0.0 (8)';
    const POWERED_BY = 'RocketShip 1.0.0 (8)';

    /**
     *
     * Events instance
     * @var \RocketShip\Event
     *
     */
    public $events;

    /**
     *
     * Session instance
     * @var \RocketShip\Session\Session
     *
     */
    public $session;

    /**
     *
     * Configuration
     * @var \stdClass
     *
     */
    public $config;

    /**
     *
     * Constants
     * @var \stdClass
     *
     */
    public $constants;

    /**
     *
     * Environment
     * @var String
     *
     */
    public $environment = "";

    /**
     *
     * Application's url
     * @var String
     *
     */
    public $site_url = "";

    /**
     *
     * The directory where the application is (if not a docroot)
     * @var String
     *
     */
    public $url_path = "";

    /**
     *
     * Root path
     * @var String
     *
     */
    public $root_path = "";

    /**
     *
     * The current requested uri
     * @var String
     *
     */
    public $uri = "";

    /**
     *
     * Current domain
     * @var String
     *
     */
    public $domain = "";

    /**
     *
     * Current detected route
     * @var \stdClass
     *
     */
    public $route;

    /**
     *
     * Alternate routes (for other languages)
     * @var \stdClass
     *
     */
    public $alternate_routes;

    /**
     *
     * Bundle instances
     * @var \stdClass
     *
     */
    public $bundles;

    /**
     *
     * Helper instances
     * @var \stdClass
     *
     */
    public $helpers;

    /**
     *
     * Upload manager
     *
     * @var \RocketShip\Upload
     *
     */
    public $upload;

    /**
     *
     * Locale instance
     * @var \RocketShip\Locale
     *
     */
    public $locale;

    /**
     *
     * Input instance
     * @var \RocketShip\Security\Input
     *
     */
    public $input;

    /**
     *
     * Routing instance
     * @var \RocketShip\Routing
     *
     */
    public $router;

    /**
     *
     * Application's static instance
     * @var \RocketShip\Application
     *
     */
    public static $instance;

    /**
     *
     * API
     * @var \RocketShip\Api
     *
     */
    public $api;

    /**
     *
     * Environment static version
     * @var
     *
     */
    public static $_environment = "";

    /**
     *
     * Create a new application
     *
     * @access  public
     * @final
     *
     */
    final public function __construct()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }

        $this->bundles  = new \stdClass;
        $this->helpers  = new \stdClass;
        self::$instance = $this;

        $this->autoload();
        $this->events = new Event;

        Configuration::loadAppConfigurations();

        $this->config    = Configuration::getObject('configuration');
        $this->constants = Configuration::getObject('constants');

        /* CORS Support */
        if ($this->config->cors->allow != 'none' && php_sapi_name() != 'cli') {
            header('Access-Control-Allow-Origin: ' . $this->config->cors->allow);
            header('Access-Control-Allow-Methods: ' . implode(",", $this->config->cors->methods));
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: X-Requested-With');
        }

        /* Set timezone if not set */
        date_default_timezone_set($this->config->general->timezone);

        /* Set UTF-8 for document charset (header) */
        if (php_sapi_name() != 'cli') {
            header('X-Powered-By: ' . self::POWERED_BY);
        }
    
        /* Setting memory limit of the PHP process */
        ini_set('memory_limit', Configuration::get('configuration', 'performance.memory_limit'));
        
        /* Environment */
        $this->setupEnvironment();

        /* Debugging */
        $this->setupDebugging();

        /* Session management */
        $handler = new Session($this->config->development->session);
        session_set_save_handler(
            array($handler, 'open'),
            array($handler, 'close'),
            array($handler, 'read'),
            array($handler, 'write'),
            array($handler, 'destroy'),
            array($handler, 'garbageCollect')
        );

        /* Prevent problems since we are using OO for session management */
        register_shutdown_function('session_write_close');
        session_start();
        $this->session = $handler;

        /* Routing (load routes) */
        $this->router = new Routing;
        $this->router->loadAppRoutes();

        /* Register directives */
        Directives::loadAll();

        /* Load bundles */
        $this->loadBundles();
                
        /* Load helpers */
        $this->loadHelpers();

        /* Request data (client, ip, request type, etc.) */
        $request       = new Request;
        $this->request = $request;

        $this->events->trigger("post-bundles", null, 'event');

        /* Find route */
        $this->route = $this->router->find($this->uri);

        if (!empty($this->route)) {
            $this->alternate_routes = $this->router->alternate();
        }
    
        /* Locale */
        Locale::enforceLocale();
        Locale::loadAll();
        $this->locale = new Locale;

        /* Make sure temp folder is writable by the server */
        IO::isDirectoryWritable($this->root_path . '/app/tmp/', true, false, true);

        /* Input */
        $this->input = new Input;

        /* Authenticate the API user if the url is the right one */
        $this->api = new Api;
        $this->api->authenticate($this->uri);
    }

    /**
     *
     * Run the application
     *
     * @access  public
     * @final
     *
     */
    final public function run()
    {
        $this->events->trigger('pre-controller', null, 'event');

        $lang = $this->session->get('app_language');
        setlocale(LC_ALL, $this->config->localization->{$lang} . '.utf8');

        if (!empty($this->route->action)) {
            list($method, $controller) = explode('@', $this->route->action);

            $is_api = false;
            if (!empty($this->route->api)) {
                /* Route is an API call, validate token and used verb */
                $this->api->validateToken();
                $this->api->validateVerb($this->route->verbs);
                $this->events->trigger('api-authenticated', null, 'event');
                $is_api = true;
            }

            $name  = ucfirst(strtolower($controller)) . '.php';
            $file  = $this->route->path . '/controllers/' . $name;
            $class = ucfirst(strtolower($controller)) . 'Controller';

            if (file_exists($file)) {
                include_once $file;

                if (class_exists($class)) {
                    $instance = new $class;

                    if (method_exists($instance, $method)) {
                        call_user_func_array(array($instance, $method), $this->route->arguments);

                        if ($instance->view->rendered == false && $is_api == false) {
                            call_user_func(array($instance->view, 'render'), $method);
                        }
                    } else {
                        throw new \RuntimeException("Method '{$method}' in controller '{$name}' cannot be called, not found.'");
                    }
                } else {
                    throw new \RuntimeException("Controller '{$name}' does not respect convention, expecting class named '{$class}'");
                }
            } else {
                $path = dirname($file);
                throw new \RuntimeException("Controller '{$name}' could not be found in path {$path}.");
            }
        } else {
            $file = $this->root_path . '/app/controllers/Error.php';
            include_once $file;
            $instance = new \ErrorController;
            $instance->notFound();
        }
    }

    /**
     *
     * handle application shutdown
     *
     * @access  public
     *
     */
    public function quit()
    {
        Collection::disconnect();
        $this->events->trigger('shutdown', null, 'events');
        exit();
    }

    /**
     *
     * Setup autoloaders
     *
     * @access  private
     * @final
     *
     */
    final private function autoload()
    {
        /* Autoload composer */
        include_once dirname(__DIR__) . '/vendor/autoload.php';

        /* RocketShip autoload */
        spl_autoload_register(__NAMESPACE__ . '\Application::runAutoload');
    }

    /**
     *
     * RocketShip's custom autoload
     *
     * @param   string  class name that is being requested
     * @return  bool    success / failure
     * @final
     * @static
     *
     */
    final static function runAutoload($class)
    {
        /* Remove namespace from class name */
        $class = str_replace('\\', '/', $class);

        /* Look in the RocketShip system folder */
        if (file_exists(dirname(__DIR__) . '/' . $class . '.php')) {
            include_once dirname(__DIR__) . '/' . $class . '.php';
            return true;
        }

        /* Look for models */
        if (file_exists(dirname(__DIR__) . '/app/models/' . $class . '.php')) {
            include_once dirname(__DIR__) . '/app/models/' . $class . '.php';
            return true;
        }

        return false;
    }

    /**
     *
     * Setup the application environment variables (host, environment, site url, etc.)
     *
     * @access  private
     *
     */
    private function setupEnvironment()
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = 'CLI';
        }

        if ($_SERVER['HTTP_HOST'] == 'CLI') {
            $this->environment  = Configuration::get('definition', 'cli');
            self::$_environment = $this->environment;
            $this->site_url     = 'http://';
            $this->root_path    = dirname(__DIR__);
            return;
        }

        $protocol = Request::HTTP;

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocol = Request::HTTPS;
        }
        
        $uri    = explode('?', $_SERVER['REQUEST_URI']);
        $uri[0] = str_replace('.html', '', $uri[0]);

        $this->root_path = dirname(__DIR__);
        $this->url_path  = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->root_path);
        $this->site_url  = $protocol . '://' . $_SERVER['HTTP_HOST'] . $this->url_path;
        $this->uri       = str_replace('//', '/', str_replace($this->url_path, '/', $uri[0]));
        $this->domain    = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $stage  = Configuration::get('definition', 'environments.staging');
        $prod   = Configuration::get('definition', 'environments.production');

        if (in_array($domain, $stage)) {
            $this->environment = 'staging';
        } elseif (in_array($domain, $prod)) {
            $this->environment = 'production';
        } else {
            $this->environment = 'development';
        }
        
        self::$_environment = $this->environment;
    }

    /**
     *
     * Setup the debugging system
     *
     * @access  private
     *
     */
    private function setupDebugging()
    {
        ini_set('display_errors', 'On');
//        if ($this->environment != 'production' && $this->config->development->debugging == 'yes') {
//            error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);
//
//            $whoops      = new Run();
//            $errorPage   = new PrettyPageHandler;
//            $jsonHandler = new JsonResponseHandler;
//
//            $errorPage->setPageTitle('RocketShip Exception');
//            $errorPage->setEditor('sublime');
//            $errorPage->addDataTable('Platform', array(
//                'RocketShip version' => self::VERSION,
//                'PHP version'     => phpversion() . '-' . PHP_OS
//            ));
//
//            $jsonHandler->onlyForAjaxRequests(true);
//            $whoops->pushHandler($errorPage);
//            $whoops->pushHandler($jsonHandler);
//            $whoops->register();
//
//            ini_set('display_errors', 'On');
//        } else {
//            error_reporting(0);
//            ini_set('display_errors', 'off');
//        }
    }

    /**
     *
     * load all bundles
     *
     * @access  private
     * @throws  \RuntimeException
     *
     */
    private function loadBundles()
    {
        $directory = new \RecursiveDirectoryIterator($this->root_path . '/bundles/');
        $iterator  = new \RecursiveIteratorIterator($directory);

        $files = array();
        foreach ($iterator as $info) {
            if (stristr($info->getPathname(), 'bundle.php')) {
                $files[] = $info->getPathname();
            }
        }
                
        if (!empty($files)) {
            foreach ($files as $bundle) {
                include_once $bundle;

                $name       = str_replace('.php', '', basename($bundle));
                $clean_name = strtolower(str_replace('Bundle', '', $name));
                $class      = ucfirst($name);

                if (!class_exists($class)) {
                    throw new \RuntimeException("The bundle '{$name}' is not valid bundle. It must contain the {$class} class.");
                }

                $instance = new $class;
                $parent   = get_parent_class($instance);
                
                if ($parent != 'RocketShip\Bundle') {
                    throw new \RuntimeException("The class '{$name}' is not valid bundle class. It must extend the RocketShip\\Bundle class.");
                }

                $this->bundles->{$clean_name} = $instance;

                /* Load models for that bundle */
                $models = glob(dirname($bundle) . '/models/*.php');
                if (!empty($models)) {
                    foreach ($models as $model) {
                        include_once $model;
                    }
                }

                /* Routing (load routes) */
                $this->router->loadBundleRoutes(dirname($bundle));
                
                /* Run init method */
                $instance->setBundlePath(dirname($bundle));
                $instance->init();
                $instance->verifyInstall();
            }
        }
    }

    /**
     *
     * Load all the possible helpers (system, bundle, app)
     *
     * @access  private
     *
     */
    private function loadHelpers()
    {
        /* System helpers */
        $sysfiles = glob($this->root_path . '/RocketShip/Helpers/*.php');

        foreach ($sysfiles as $file) {
            include_once $file;

            $name  = strtolower(str_replace('.php', '', basename($file)));
            $class = 'RocketShip\\Helpers\\' . ucfirst($name);

            if (class_exists($class)) {
                $this->helpers->{$name} = new $class;
            }
        }

        /* Bundle helpers */
        foreach ($this->bundles as $name => $instance) {
            $files = glob($instance->getBundlePath() . '/Helpers/*.php');

            foreach ($files as $file) {
                include_once $file;

                $name  = strtolower(str_replace('.php', '', basename($file)));
                $class = ucfirst($name);

                if (class_exists($class)) {
                    $this->helpers->{$name} = new $class;
                }
            }
        }

        /* App helpers */
        $appfiles = glob($this->root_path . '/app/Helpers/*.php');

        foreach ($appfiles as $file) {
            include_once $file;

            $name  = strtolower(str_replace('.php', '', basename($file)));
            $class = ucfirst($name);

            if (class_exists($class)) {
                $this->helpers->{$name} = new $class;
            }
        }
    }
}
