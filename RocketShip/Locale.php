<?php

namespace RocketShip;

use RocketShip\Utils\Request;
use Symfony\Component\Yaml\Yaml;

class Locale extends Base
{
    /* Current aplication's languages strings */
    private static $locale_strings;

    /* Current applications locale */
    private static $current_locale;

    /* Locale code that should override the uri's locale */
    private static $alternate_locale;

    /**
     *
     * loadAll
     *
     * Load all the locale files for the current language
     *
     * @return     void
     * @access     public
     * @static
     *
     */
    public static function loadAll()
    {
        self::$locale_strings = [];

        /* Language override */
        if (empty(self::$alternate_locale)) {
            $app_file = dirname(__DIR__) . '/app/locale/' . self::$current_locale . '.yaml';
        } else {
            $app_file = dirname(__DIR__) . '/app/locale/' . self::$alternate_locale . '.yaml';
        }

        /* Load app locale */
        self::loadFile($app_file);

        /* Load all bundle locales */
        $bundles = Application::$instance->bundles;
        foreach ($bundles as $bundle) {
            if (empty(self::$alternate_locale)) {
                $file = $bundle->getBundlePath() . '/locale/' . self::$current_locale . '.yaml';
            } else {
                $file = $bundle->getBundlePath() . '/locale/' . self::$alternate_locale . '.yaml';
            }

            self::loadFile($file);
        }

        Application::$instance->events->trigger('post-locale', null);
    }

    /**
     *
     * loadFile
     *
     * Load the local file and parse it
     * Supports 2 styles : string : value or namespaced.string : value
     *
     * @param     string     file to parse
     * @return    void
     * @access    private
     * @static
     *
     */
    private static function loadFile($file)
    {
        if (file_exists($file)) {
            $strings = Yaml::parse($file);

            if (empty(self::$locale_strings)) {
                self::$locale_strings = [];
            }

            if (!empty($strings)) {
                self::$locale_strings = array_merge(self::$locale_strings, $strings);
            }
        }
    }

    /**
     *
     * getString
     *
     * Get a locale string, if it's not found, the key will be returned so it can be
     * easily spotted in the template.
     *
     * @param     string     dot formatted string (ex: site.title)
     * @return    string     result
     * @access    private
     * @static
     *
     */
    private static function getString($string)
    {
        $indexes = explode(".", $string);

        $strings = self::$locale_strings;
        foreach ($indexes as $index) {
            if (!empty($strings[$index])) {
                $strings = $strings[$index];
            } else {
                return $string;
            }
        }

        return $strings;
    }

    /**
     *
     * findString
     *
     * Find a string within the locale strings
     *
     * @param   string  the string to find (no dotted notation)
     * @return  string  the value of the key found or key is returned
     * @access  private
     * @static
     *
     */
    private static function findString($string)
    {
        $strings = self::$locale_strings;

        foreach ($strings as $key => $string_group) {
            foreach ($string_group as $index => $value) {
                if (!is_string($value)) {
                    foreach ($value as $key => $subvalue) {
                        if ($key == $string) {
                            return $value;
                        }
                    }
                } elseif ($index == $string) {
                    return $value;
                }
            }
        }

        return $string;
    }

    /**
     *
     * setCurrentLocale
     *
     * Set the current locale code
     *
     * @param   string     code to use (fr, en, etc..)
     * @return  void
     * @access  public
     * @static
     *
     */
    public static function setCurrentLocale($code)
    {
        self::$current_locale = $code;
    }

    /**
     *
     * setOverrideLocale
     *
     * Override the current route's locale code
     *
     * @param   string     code to use (fr, en, etc..)
     * @return  void
     * @access  public
     * @static
     *
     */
    public static function setOverrideLocale($code)
    {
        self::$alternate_locale = $code;
    }

    /**
     *
     * switchLocale
     *
     * Change the current locale language and reload the language strings
     *
     * @param   string  the language code
     * @access  public
     * @static
     *
     */
    public static function switchLocale($code)
    {
        $_SESSION['app_language'] = $code;
        self::setCurrentLocale($code);
        self::loadAll();
    }

    /**
     *
     * t
     *
     * Output a translation string
     *
     * @param    string     dot-notated string
     * @param    array      array of variables or string to use
     * @access   public
     *
     */
    public function t($string, $variables=array())
    {
        if (!empty($variables)) {
            if (is_string($variables)) {
                $variables = array($variables);
            }

            echo vsprintf(self::getString($string), $variables);
        } else {
            echo self::getString($string);
        }
    }

    /**
     *
     * get
     *
     * Get a translation string
     *
     * @param    string     notation string
     * @param    array      array of variables or string to use
     * @return   string     the translation string
     * @access   public
     *
     */
    public function get($string, $variables=array())
    {
        if (!empty($variables)) {
            if (is_string($variables)) {
                $variables = array($variables);
            }

            return vsprintf(self::getString($string), $variables);
        } else {
            return self::getString($string);
        }
    }

    /**
     *
     * find__
     *
     * Find a translation string
     *
     * @param    string     notation string (ex: string_name)
     * @param    array      array of variables or string to use
     * @return   string     the translation string
     * @access   public
     *
     */
    public function find($string, $variables=array())
    {
        if (!empty($variables)) {
            if (is_string($variables)) {
                $variables = array($variables);
            }

            return vsprintf(self::findString($string), $variables);
        } else {
            return self::findString($string);
        }
    }

    /**
     *
     * enforceLocale
     *
     * Enforce a language by domain
     *
     * @param   string  the redirect uri (required if you call it manually)
     * @param   string  locale code (required if you call it manually)
     * @return  null
     * @access  public
     * @static
     *
     */
    public static function enforceLocale($redirect=null, $locale=null)
    {
        $app   = Application::$instance;
        $uri   = $app->uri;
        $host  = $app->site_url;
        $conf  = Configuration::get('locale', $app->domain);
        $route = $app->route;

        /* Do not inforce on this route */
        if (!empty($route) && (empty($route->enforce) || $route->enforce == 'no')) {
            return null;
        }

        if (empty($locale)) {
            $locale = self::$current_locale;
        }

        if (!empty($conf)) {
            foreach ($conf as $domain => $info) {
                if ($domain == $host) {
                    if ($info->locale == $locale) {
                        return null;
                    } else {
                        //$url = new URL;

                        $protocol = self::$app->request->protocol;

                        if ($protocol == Request::HTTPS) {
                            if (empty($redirect)) {
                                $redirect = 'https://' . $info->redirect . $uri;
                            } else {
                                $redirect = 'https://' . $info->redirect . $redirect;
                            }

                            header('location: ' . $redirect);
                            exit();
                        } else {
                            if (empty($redirect)) {
                                $redirect = 'http://' . $info->redirect . $uri;
                            } else {
                                $redirect = 'http://' . $info->redirect . $redirect;
                            }

                            header('location: ' . $redirect);
                            exit();
                        }
                    }
                }
            }
        }
    }
}
