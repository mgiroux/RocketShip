<?php

namespace RocketShip;

class Routing extends Base
{
    private static $routes          = [];
    private static $route_arguments = [];
    private static $patterns        = [];

    /**
     *
     * Flag that marks the user requested a json render
     *
     * @var Bool
     *
     */
    public static $json_flag = false;

    /**
     *
     * Path of origin of the current route (origin = path to file)
     * @var String
     *
     */
    public static $current_path = "";

    /**
     *
     * Load application routes
     *
     * @access  public
     *
     */
    public function loadAppRoutes()
    {
        self::$routes = new \stdClass;

        $file = $this->app->root_path . '/app/configurations/routes.yaml';

        if (file_exists($file)) {
            $data = Configuration::loadFile($file);

            if (!empty($data->patterns)) {
                self::$patterns = $data->patterns;
                unset($data->patterns);
            }

            foreach ($data as $num => $route) {
                foreach ($route->uri as $lang => $uri) {
		            $route->uri->{$lang} = (substr($uri, -1, 1) == '/') ? substr($uri, 0, strlen($uri) - 1) : $uri;
	            }
                $route->path = $this->app->root_path . '/app';
            }

            self::$routes = $data;
        }
    }

    /**
     *
     * Load bundle routes
     *
     * @param   string  the bundle's path
     * @access  public
     *
     */
    public function loadBundleRoutes($path)
    {
        $file = $path . '/configurations/routes.yaml';

        if (file_exists($file)) {
            $data = Configuration::loadFile($file);
            $name = strtolower(basename($path));

            $test = (array)$data;

            if (!empty($test)) {
                if (!empty($data->patterns)) {
                    self::$patterns = array_merge(self::$patterns, $data->patterns);
                    unset($data->patterns);
                }

                foreach ($data as $key => $route) {
                    foreach ($route->uri as $lang => $uri) {
                        $route->uri->{$lang} = (substr($uri, -1, 1) == '/') ? substr($uri, 0, strlen($uri) - 1) : $uri;
                    }
                    $route->path = $path;
                    
                    self::$routes->{$key} = $route;
                }
            }
        }        
    }

    /**
     *
     * Find the route that matches the current url
     *
     * @param   string  the url to try to match
     * @return  object  the route data
     * @access  public
     *
     */
    public function find($url)
    {
        $url = (substr($url, -1, 1) == '/') ? substr($url, 0, strlen($url) - 1) : $url;

        /* Static routes */
        if (!empty(self::$routes)) {
            foreach (self::$routes as $key => $permalink) {
                $matched = self::matchURI($permalink, $url);

                if ($matched) {
                    $this->app->session->set('app_language', $matched['language']);
                    Locale::setCurrentLocale($matched['language']);

                    /* this url is secure, if it's not on HTTPS, force it */
                    if ($permalink->secure == 'yes') {
                        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
                            $url = str_replace("http://", "https://", $this->app->site_url) . $url;
                            header('location: ' . $url);
                            exit();
                        }
                    }

                    $permalink->arguments = [];
                    self::$current_path  = $permalink->path;
                    return $permalink;
                }
            }
        }

        /* Check possible dynamic routes (:num, :param, :any or pattern) */
        if (!empty(self::$routes)) {
            foreach (self::$routes as $key => $permalink) {
                $is_wild = self::isWild($permalink->uri);

                if ($is_wild) {
                    /* Replace easy-wildcards by regex params */
                    $targets = ['(:num)', '(:any)', '(:string)', '(:mongoid)'];
                    $replace = ['([0-9]+)', '([0-9a-zA-Z\.\-\_\/\:\=]+)', '([a-zA-Z]+)', '([0-9a-fA-F]{24})'];

                    foreach ($permalink->uri as $lang => $the_uri) {
                        $pattern = '/(\\()((?:[a-zA-Z0-9]*))(\\))/';
                        preg_match_all($pattern, $the_uri, $matches);

                        if (!empty($matches[2])) {
                            foreach ($matches[2] as $num => $match) {
                                $the_uri = str_replace($matches[0][$num], self::$patterns->{$match}, $the_uri);
                            }
                        }

                        $the_rule = str_replace($targets, $replace, $the_uri);

                        if (preg_match('#^' . $the_rule . '$#', $url, $matches)) {
                            $rule     = $the_rule;
                            $the_lang = $lang;
                            break;
                        }
                    }

                    if (!empty($rule)) {
                        if (preg_match('#^' . $rule . '$#', $url, $matches)) {
                            unset($matches[0]);
                            $arguments = array_values($matches);

                            /* Filter values to be strings (won't affect numbers) */
                            foreach ($arguments as $num => $argument) {
                                $arguments[$num] = filter_var($argument, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                            }

                            self::$route_arguments = $arguments;

                            if (!empty($the_lang)) {
                                $this->app->session->set('app_language', $the_lang);
                                Locale::setCurrentLocale($the_lang);
                            }

                            /* this url is secure, if it's not on HTTPS, force it */
                            if ($permalink->secure == 'yes') {
                                if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
                                    $url = str_replace("http://", "https://", $this->app->site_url) . $url;
                                    header('location: ' . $url);
                                    exit();
                                }
                            }

                            $permalink->arguments = [];
                            if (!empty(self::$route_arguments)) {
                                $permalink->arguments = self::$route_arguments;
                            }

                            self::$current_path = $permalink->path;
                            return $permalink;
                        }
                    }
                }
            }
        }

        if (!empty(self::$routes)) {
            foreach (self::$routes as $key => $permalink) {
                if (is_string($permalink->uri) && ($permalink->uri == '/*/' || $permalink->uri == '*')) {
                    /* Catch all route (*) */
                    if (!empty($matched['language'])) {
                        $this->app->session->set('app_language', $matched['language']);
                        Locale::setCurrentLocale($matched['language']);
                    }

                    /* this url is secure, if it's not on HTTPS, force it */
                    if ($permalink->secure == 'yes') {
                        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
                            $url = str_replace("http://", "https://", $this->app->site_url) . $url;
                            header('location: ' . $url);
                            exit();
                        }
                    }

                    $permalink->arguments = [];
                    self::$current_path = $permalink->path;
                    return $permalink;
                }
            }
        }
    }

    public function alternate($requested_route=null)
    {
        $route = empty($requested_route) ? $this->app->route : $requested_route;

        $output = new \stdClass;
        foreach ($route->uri as $lang => $uri) {
            if ($this->app->session->get('app_language') != $lang) {
                $output->{$lang} = $this->app->site_url . $uri;
            }
        }

        return $output;
    }

    /**
     *
     * Try to match the current uri with one in the given route
     *
     * @param   array   route array
     * @param   string  current URI
     * @return  mixed   null if not matched, array with language value if matched
     * @access  public
     * @static
     *
     */
    private static function matchURI($urilist, $uri)
    {
        foreach ($urilist->uri as $lang => $the_uri) {
            if ($the_uri == $uri || $the_uri . '/' == $uri || $uri . '/' == $the_uri || $the_uri . '.html' == $uri || $the_uri . '.json' == $uri) {
                if (stristr($uri, '.json')) {
                    self::$json_flag = true;
                }

                return ['language' => $lang];
            }
        }

        return null;
    }

    /**
     *
     * Check if the uri or uris have wildcard characters in it/them
     *
     * @param   mixed   string/array of uris
     * @return  bool    true/false
     * @access  public
     * @static
     *
     */
    private static function isWild($uri)
    {
        $pattern_regex = '/(\\()((?:[a-zA-Z0-9]*))(\\))/';

        foreach ($uri as $lang => $the_uri) {
            if (stristr($the_uri, '(:any)') || stristr($the_uri, '(:num)') || stristr($the_uri, '(:string)' || stristr($the_uri, '(:mongoid)'))) {
                return true;
            }
        }

        foreach ($uri as $lang => $the_uri) {
            preg_match_all($pattern_regex, $the_uri, $matches);

            if (!empty($matches[2])) {
                return true;
            }
        }

        return false;
    }

    /* Useful application API */

    /**
     *
     * Parse a dynamic route into a useable url
     *
     * @param $route
     * @param   array    arguments to
     * @return  string   the url if it exists
     *
     */
    public function to($route, $arguments=null)
    {
        if (!empty($arguments) && is_string($arguments)) {
            $arguments = [$arguments];
        }

        foreach (self::$routes as $key => $permalink) {
            if (!empty($permalink->{$route})) {
                $url      = $permalink->{$route}->uri;
                $language = ($this->app->session->get('app_language') != null) ? $this->app->session->get('app_language') : Configuration::get('configuration', 'languages.default');

                return $this->parseRoute($url->{$language}, $arguments);
            }
        }

        return '';
    }

    /**
     *
     * Did the user request a json output (/route/name.json)
     *
     * @return bool
     * @access public
     *
     */
    public function requestedJson()
    {
        return self::$json_flag;
    }

    /**
     *
     * Parse the given argument into the given route
     *
     * @param   string  url to handle
     * @param   array   arguments to parse in
     * @return  string  parsed url
     * @access  private
     *
     */
    private function parseRoute($url, $arguments)
    {
        $targets = ['(:num)', '(:any)', '(:string)', '(:mongoid)'];
        $pattern = '/(\\()((?:[a-zA-Z0-9]*))(\\))/';

        preg_match_all($pattern, $url, $matches);

        if (!empty($matches[2])) {
            foreach ($matches[2] as $num => $match) {
                $url = str_replace($matches[0][$num], self::$patterns->{$match}, $url);
            }
        }

        $url = str_replace($targets, ['%d', '%s', '%s', '%s'], $url);
        return vsprintf($url, $arguments);
    }
}
