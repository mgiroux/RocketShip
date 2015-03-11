<?php

namespace RocketShip;

/* Primitive types loading */
require_once __DIR__ . '/Primitives/String.php';
require_once __DIR__ . '/Primitives/Number.php';
require_once __DIR__ . '/Primitives/Collection.php';
use RocketShip\Database\Collection;
use RocketShip\Utils\IO;
use RocketShip\Utils\Request;
use RocketShip\Security\Input;
use RocketShip\Session;
use RocketShip\Directives;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;
use String;
use Number;
use Collection AS stdCollection;

class Application
{
    const VERSION    = '1.1.0 (8)';
    const POWERED_BY = 'RocketShip 1.1.0 (8)';

    /**
     *
     * Events instance
     * @var \RocketShip\Event
     *
     */
    public $events;

    /**
     *
     * Filters instane
     * @var \RocketShip\Filter
     *
     */
    public $filters;

    /**
     *
     * Session instance
     * @var \RocketShip\Session
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
            $_SERVER['REQUEST_URI'] = String::init('/');
        }

        $this->bundles  = new \stdClass;
        $this->helpers  = new \stdClass;
        self::$instance = $this;

        $this->autoload();

        $this->events  = new Event;
        $this->filters = new Filter;

        Configuration::loadAppConfigurations();

        $this->config    = Configuration::getObject(String::init('configuration'));
        $this->constants = Configuration::getObject(String::init('constants'));

        /* CORS Support */
        if ($this->config->cors->allow != 'none' && php_sapi_name() != 'cli') {
            header('Access-Control-Allow-Origin: ' . $this->config->cors->allow);
            header('Access-Control-Allow-Methods: ' . $this->config->cors->methods->join(","));
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
        ini_set('memory_limit', $this->config->performance->memory_limit);

        /* Pre environment setup */
        $this->events->trigger(Event::CORE_PRE_SETUP, null);

        /* Environment */
        $this->setupEnvironment();

        /* Debugging */
        $this->setupDebugging();

        /* Pre routes */
        $this->events->trigger(Event::CORE_PRE_ROUTES, null);

        /* Routing (load routes) */
        $this->router = new Routing;
        $this->router->loadAppRoutes();

        /* Post routes, Pre Directives */
        $this->events->trigger(Event::CORE_POST_ROUTES, null);
        $this->events->trigger(Event::CORE_PRE_DIRECTIVES, null);

        /* Register directives */
        Directives::loadAll();

        /* Post directives, Pre bundles */
        $this->events->trigger(Event::CORE_POST_DIRECTIVES, null);
        $this->events->trigger(Event::CORE_PRE_BUNDLES, null);

        /* Load bundles */
        $this->loadBundles();

        /* Pre Helpers */
        $this->events->trigger(Event::CORE_PRE_HELPERS, null);
                
        /* Load helpers */
        $this->loadHelpers();

        /* Post Helpers */
        $this->events->trigger(Event::CORE_POST_HELPERS, null);

        $this->upload = new Upload;

        /* Pre session */
        $this->events->trigger(Event::CORE_PRE_SESSION, null);

        /* Session management */
        $handler = new Session($this->config->development->session);
        session_set_save_handler(
            [$handler, 'open'],
            [$handler, 'close'],
            [$handler, 'read'],
            [$handler, 'write'],
            [$handler, 'destroy'],
            [$handler, 'garbageCollect']
        );

        /* Prevent problems since we are using OO for session management */
        register_shutdown_function('session_write_close');
        session_start();
        $this->session = $handler;

        /* Request data (client, ip, request type, etc.) */
        $request       = new Request;
        $this->request = $request;

        /* Post bundles */
        $this->events->trigger(Event::CORE_POST_BUNDLES, null);

        /* Handle possible /public/uploads/.... if driver is mongo */
        if ($this->config->uploading->driver->equals('mongodb')) {
            if (!empty($this->uri) && $this->uri->contains('/public/uploads/files/')) {
                $return = $this->handleStaticFile(basename($this->uri));

                if ($return) {
                    $this->quit();
                }
            }
        }

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
        $this->events->trigger(Event::CORE_PRE_CONTROLLER, null);

        $lang = $this->session->get('app_language');
        setlocale(LC_ALL, $this->config->localization->{$lang} . '.utf8');

        $ip        = $_SERVER['REMOTE_ADDR'];
        $forbidden = false;

        if (!empty($this->route->allow) && $this->route->allow != 'all' && $this->route->allow != $ip) {
            $forbidden = true;
        }

        if (is_array($this->route->allow) && !in_array($ip, $this->route->allow)) {
            $forbidden = true;
        } else {
            $forbidden = false;
        }

        /* Permissions */
        if ($forbidden) {
            $file = $this->root_path . '/app/controllers/Error.php';
            include_once $file;

            $instance = new \ErrorController;
            $instance->forbidden();

            call_user_func([$instance->view, 'render'], 'forbidden');
            $this->quit();
        }

        if (!empty($this->route->action) && !$this->route->action->isEmpty()) {
            $action = $this->route->action->split('@');
            $is_api = false;

            if (!empty($this->route->api)) {
                /* Route is an API call, validate token and used verb */
                $this->api->validateToken();
                $this->api->validateVerb($this->route->verbs->raw());
                $this->events->trigger(Event::CORE_API_AUTH, null);
                $is_api = true;
            }

            $name  = $action->{1}->lower()->capitalize()->append('.php');
            $file  = $this->route->path->append('/controllers/' . $name);
            $class = $action->{1}->lower()->capitalize()->append('Controller')->raw();

            if (file_exists($file)) {
                include_once $file;

                if (class_exists($class)) {
                    $instance = new $class;

                    if (method_exists($instance, $action->{0}->raw())) {
                        $method = $action->{0}->raw();

                        call_user_func_array([$instance, $method], $this->route->arguments->raw());

                        if ($instance->view->rendered == false && $is_api == false) {
                            call_user_func([$instance->view, 'render'], $action->{0});
                        }

                        $this->events->trigger(Event::CORE_POST_CONTROLLER, $instance);
                    } else {
                        throw new \RuntimeException("Method '{$action->{0}}' in controller '{$name}' cannot be called, not found.'");
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

            call_user_func([$instance->view, 'render'], 'notFound');
            $this->quit();
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
        $this->events->trigger(Event::CORE_SHUTDOWN, null);
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
            $_SERVER['HTTP_HOST'] = String::init('CLI');
        }

        if ($_SERVER['HTTP_HOST'] == 'CLI') {
            $this->environment  = String::init(Configuration::get('definition', 'cli'));
            self::$_environment = String::init($this->environment);
            $this->site_url     = String::init('http://');
            $this->root_path    = String::init(dirname(__DIR__));
            return;
        }

        $protocol = String::init(Request::HTTP);

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocol = String::init(Request::HTTPS);
        }

        $uri = String::init($_SERVER['REQUEST_URI']);
        $uri = $uri->split('?');
        $uri->set(0, $uri->{0}->replace('.html', ''));

        $this->root_path = String::init(dirname(__DIR__));
        $this->url_path  = $this->root_path->replace($_SERVER['DOCUMENT_ROOT'], '');
        $this->site_url  = String::init($protocol . '://' . $_SERVER['HTTP_HOST'] . $this->url_path);

        $this->uri       = $uri->{0}->replace($this->url_path, '/')->replace('//', '/');
        $this->domain    = String::init(str_replace('www.', '', $_SERVER['HTTP_HOST']));

        $domain = String::init(str_replace('www.', '', $_SERVER['HTTP_HOST']));
        $stage  = stdCollection::init(Configuration::get('definition', 'environments.staging'));
        $prod   = stdCollection::init(Configuration::get('definition', 'environments.production'));

        if ($stage->contains($domain)) {
            $this->environment = String::init('staging');
        } elseif ($prod->contains($domain)) {
            $this->environment = String::init('production');
        } else {
            $this->environment = String::init('development');
        }
        
        self::$_environment = String::init($this->environment);
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
        if (!$this->environment->equals('production') && $this->config->development->debugging->equals('yes')) {
            error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);

            $whoops      = new Run();
            $errorPage   = new PrettyPageHandler;
            $jsonHandler = new JsonResponseHandler;

            $errorPage->setPageTitle('RocketShip Exception');
            $errorPage->setEditor('sublime');
            $errorPage->addDataTable('Platform', [
                'RocketShip version' => self::VERSION,
                'PHP version'        => phpversion() . '-' . PHP_OS
            ]);

            $jsonHandler->onlyForAjaxRequests(true);
            $whoops->pushHandler($errorPage);
            $whoops->pushHandler($jsonHandler);
            $whoops->register();

            ini_set('display_errors', 'On');
        } else {
            error_reporting(0);
            ini_set('display_errors', 'off');
        }
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

        $files = [];
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

    /**
     *
     * Handle the serving of upload files
     *
     * @param   string  file id
     * @return  bool    found, not found
     * @access  private
     *
     */
    private function handleStaticFile($id)
    {
        $file = $this->upload->getRaw($id);

        if (empty($file)) {
            /* Lower level upload id? */
            $upload = new Collection('uploads', true);
            $file   = $upload->getFileById($id);
        }

        if (!empty($file)) {
            header('Content-Type: ' . $file->file['mime']);

            if ($this->config->development->anticaching == 'yes') {
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            }

            header('Pragma: public');
            header('Content-Length: ' . $file->getSize());
            $stream = $file->getResource();

            while (!feof($stream)) {
                echo fread($stream, 8192);
            }

            return true;
        }

        return false;
    }
}
