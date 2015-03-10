<?php

namespace RocketShip\Helpers;

use RocketShip\Base;
use String;

class HTML extends Base
{
    public static $injected_js  = "";
    public static $injected_css = "";

    /**
     *
     * Load css files (each argument is 1 file) (outputs the css html code directly)
     *
     * @param     string     infinite list of files (ex: css('file1', 'file2', 'file3'))
     * @access    protected
     * @final
     *
     */
    public final function css()
    {
        $files = func_get_args();
        $path  = $this->app->url_path->append('/public/app/css');

        foreach ($files as $file) {
            switch ($file)
            {
                case "cdn:normalize":
                    echo '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.2/normalize.min.css">' . "\n";
                    break;

                case "cdn:bootstrap":
                    echo '<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">' . "\n";
                    break;

                case "jquery-ui":
                    echo '<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css">' . "\n";
                    break;

                default:
                    if (!strstr($file, '.css')) {
                        if (!stristr($file, '.htc')) {
                            /* Add .css if not added (lazy loading) */
                            $file .= '.css';
                        }
                    }

                    if (file_exists($this->app->root_path . $path . '/' . $file)) {
                        /* Add file modification time only for developement and staging environments (helps with debugging) */
                        if ($this->app->config->development->anticaching->equals('yes')) {
                            $time = filemtime($path . '/' . $file);
                            echo '<link rel="stylesheet" href="' . $this->app->site_url . $path . '/' . $file . '?' . $time . '">' . "\n";
                        } else {
                            echo '<link rel="stylesheet" href="' . $this->app->site_url . $path . '/' . $file . '">' . "\n";
                        }
                    }
                    break;
            }
        }
    }

    /**
     *
     * Load stylesheets from specified bundle path
     *
     * @param   string  bundle name as first argument
     * @param   mixed   indefinite list of css to load
     * @access  public
     * @final
     *
     */
    public final function bundlecss()
    {
        $args   = func_get_args();
        $bundle = $args[0];
        unset($args[0]);

        $path  = $this->app->url_path->append('/public/' . $bundle . '/css');

        foreach ($args as $file) {
            if (!strstr($file, '.css')) {
                if (!stristr($file, '.htc')) {
                    /* Add .css if not added (lazy loading) */
                    $file .= '.css';
                }
            }

            if (file_exists($this->app->root_path . $path . '/' . $file)) {
                if ($this->app->config->development->anticaching->equals('yes')) {
                    /* Add file modification time only for developement and staging environments (helps with debugging) */
                    if (\RocketShip\Configuration::get('configuration', 'development.anticaching') == 'yes') {
                        $time = filemtime($path . '/' . $file);
                        echo '<link rel="stylesheet" href="' . $this->app->site_url . $path . '/' . $file . '?' . $time . '">' . "\n";
                    } else {
                        echo '<link rel="stylesheet" href="' . $this->app->site_url . $path . '/' . $file . '">' . "\n";
                    }
                }
            }
        }
    }

    /**
     *
     * Load javascript files (each argument is 1 file) (outputs the javascript html code directly)
     *
     * @param     string     infinite list of files (ex: javascript('file1', 'file2', 'file3'))
     * @access    protected
     * @final
     *
     */
    public final function js()
    {
        $files = func_get_args();
        $path  = $this->app->url_path->append('/public/app/javascript');

        foreach ($files as $file) {
            /* Handle special CDNs for jquery, jquery-ui, bootstrap, angular */
            switch ($file)
            {
                case "cdn:jquery":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>' . "\n";
                    break;

                case "cdn:jquery-ui":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>' . "\n";
                    break;

                case "cdn:bootstrap":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js"></script>' . "\n";
                    break;

                case "cdn:angular":
                    echo '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.11/angular.min.js"></script>' . "\n";
                    break;

                case "cdn:angular-common":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.11/angular.min.js"></script>' . "\n";
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.10/angular-route.min.js"></script>' . "\n";
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.11/angular-sanitize.min.js"></script>' . "\n";
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.11/angular-resource.min.js"></script>' . "\n";
                    break;

                case "cdn:three":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/three.js/r70/three.min.js"></script>' . "\n";
                    break;

                case "cdn:tweenmax":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/gsap/1.15.1/TimelineMax.min.js"></script>' . "\n";
                    break;

                case "cdn:tweenlite":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/gsap/1.15.1/TweenLite.min.js"></script>' . "\n";
                    break;

                case "cdn:tinycolor":
                    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/tinycolor/1.1.1/tinycolor.min.js"></script>' . "\n";
                    break;

                default:
                    if (!strstr($file, '.js')) {
                        /* Add .js if not added (lazy loading) */
                        $file .= '.js';
                    }

                    if (file_exists($this->app->root_path . $path . '/' . $file)) {
                        /* Add file modification time only for developement and staging environments (helps with debugging) */
                        if ($this->app->config->development->anticaching->equals('yes')) {
                            $time = filemtime($path . '/' . $file);
                            echo '<script type="text/javascript" src="' . $this->app->site_url . $path . '/' . $file . '?' . $time . '"></script>' . "\n";
                        } else {
                            echo '<script type="text/javascript" src="' . $this->app->site_url . $path . '/' . $file . '"></script>' . "\n";
                        }
                    }
                    break;
            }
        }
    }

    /**
     *
     * Load javascripts from specified bundle path
     *
     * @param   string  bundle name as first argument
     * @param   mixed   indefinite list of javascripts to load
     * @access  public
     * @final
     *
     */
    public final function bundlejs()
    {
        $args   = func_get_args();
        $bundle = $args[0];
        unset($args[0]);

        $path  =  $this->app->url_path->append('/public/' . $bundle . '/javascript');

        foreach ($args as $file) {
            if (!strstr($file, '.js')) {
                /* Add .js if not added (lazy loading) */
                $file .= '.js';
            }

            if (file_exists($_SERVER['DOCUMENT_ROOT'] .  $path . '/' . $file)) {
                /* Add file modification time only for developement and staging environments (helps with debugging) */
                if ($this->app->config->development->anticaching->equals('yes')) {
                    $time = filemtime($path . '/' . $file);
                    echo '<script type="text/javascript" src="' . $this->app->site_url . $path . '/' . $file . '?' . $time . '"></script>' . "\n";
                } else {
                    echo '<script type="text/javascript" src="' . $this->app->site_url . $path . '/' . $file . '"></script>' . "\n";
                }
            }
        }
    }

    /**
     *
     * Load an image from the views public images path (outputs directly)
     *
     * @param     string     image source
     * @access    protected
     * @final
     *
     */
    public final function image($src)
    {
        echo $this->app->site_url . '/public/app/images/' . $src;
    }

    /**
     *
     * Load an image from the bundle's public images path (outputs directly)
     *
     * @param     string     the bundle name
     * @param     string     image source
     * @access    protected
     * @final
     *
     */
    public final function bundleimage($bundle, $src)
    {
        echo $this->app->site_url . '/public/' . $bundle . '/images/' . $src;
    }

    /**
     *
     * Get a path only to special public file.
     *
     * @param   string  file to get the path to
     * @access  public
     * @final
     *
     */
    public final function fromPublicPath($file)
    {
        echo $this->app->site_url . '/public/app/' . $file;
    }

    /**
     *
     * Inject Javascript at view rendering
     *
     * @param   array   files to load
     * @access  public
     * @final
     *
     */
    public final function injectJS($files)
    {
        $files = Base::toRaw($files);

        if (!is_array($files)) {
            $files = [$files];
        }

        ob_start();
        call_user_func_array([$this, 'js'], $files);
        self::$injected_js .= ob_get_clean();
    }

    /**
     *
     * Inject Stylesheets at view rendering
     *
     * @param   array   files to load
     * @access  public
     * @final
     *
     */
    public final function injectCSS($files)
    {
        $files = Base::toRaw($files);

        if (!is_array($files)) {
            $files = [$files];
        }

        ob_start();
        call_user_func_array([$this, 'css'], $files);
        self::$injected_css .= ob_get_clean();
    }

    /**
     *
     * Inject Bundle's Javascript at view rendering
     *
     * @param   array   files to load
     * @access  public
     * @final
     *
     */
    public final function injectBundleJS($bundle, $files)
    {
        $bundle = Base::toRaw($bundle);
        $files  = Base::toRaw($files);

        if (!is_array($files)) {
            $files = [$files];
        }

        ob_start();
        array_unshift($files, $bundle);

        call_user_func_array([$this, 'bundlejs'], $files);
        self::$injected_js .= ob_get_clean();
    }

    /**
     *
     * Inject Bundle's Stylesheets at view rendering
     *
     * @param   array   files to load
     * @access  public
     * @final
     *
     */
    public final function injectBundleCSS($bundle, $files)
    {
        $bundle = Base::toRaw($bundle);
        $files  = Base::toRaw($files);

        if (!is_array($files)) {
            $files = [$files];
        }

        ob_start();
        array_unshift($files, $bundle);

        call_user_func_array([$this, 'bundlecss'], $files);
        self::$injected_css .= ob_get_clean();
    }

    /**
     *
     * formatDate
     *
     * Format given unix timestamp to readable date
     *
     * @param   long    unix timestamp
     * @param   bool    show hours
     * @param   string  language code
     * @param   bool    output directly
     * @param   bool    show day in output
     * @param   bool    show year
     * @return  string  formatted date
     * @access  public
     *
     */
    public function formatDate($date, $hours=false, $lang='fr', $output=true, $showday=false, $showyear=true)
    {
        $date = Base::toRaw($date);
        $lang = Base::toRaw($lang);

        if ($lang == 'fr') {
            $months = [
                'null', "Janvier", "F&eacute;vrier",
                "Mars", "Avril", "Mai",
                "Juin", "Juillet", "Ao&ucirc;t",
                "Septembre", "Octobre", "Novembre",
                "D&eacute;cembre",
                "01" => "Janvier", "02" => "F&eacute;vrier",
                "03" => "Mars",  "04" => "Avril",
                "05" => "Mai", "06" => "Juin",
                "07" => "Juillet", "08" => "Ao&ucirc;t",
                "09" => "Septembre", "10" => "Octobre",
                "11" => "Novembre", "12" => "D&eacute;cembre"
            ];

            $days = [
                0 => 'Dimanche',
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi'
            ];

            $month = $months[date("m", $date)];
            $day   = date("j", $date);

            /* Cardinality */
            if ($day == "1") {
                $day .= 'er';
            }

            $year = date("Y", $date);

            if ($hours) {
                $time = date(" H\hi", $date);
            } else {
                $time = null;
            }

            if ($output) {
                if ($showday) {
                    echo $days[date('w', $date)] . ", ";
                }

                if ($showyear) {
                    echo $day . " " . $month . " " . $year . $time;
                } else {
                    echo $day . " " . $month . " " . $time;
                }
            } else {
                if ($showyear) {
                    if ($showday) {
                        return $days[date('w', $date)] . ", " . $day . " " . $month . " " . $year . $time;
                    } else {
                        return $day . " " . $month . " " . $year . $time;
                    }
                } else {
                    if ($showday) {
                        return $days[date('w', $date)] . ", " . $day . " " . $month . " " . $time;
                    } else {
                        return $day . " " . $month . " " . $time;
                    }
                }
            }
        } else {
            if ($showyear) {
                $y = ' Y';
            } else {
                $y = '';
            }

            if (!$hours) {
                if ($output) {
                    if ($showday) {
                        echo date('l, F jS' . $y, $date);
                    } else {
                        echo date('F jS' . $y, $date);
                    }
                } else {
                    if ($showday) {
                        return String::init(date('l, F jS' . $y, $date));
                    } else {
                        return String::init(date('F jS' . $y, $date));
                    }
                }
            } else {
                if ($output) {
                    if ($showday) {
                        echo date('l, F jS' . $y . ' H\hi', $date);
                    } else {
                        echo date('F jS' . $y . ' H\hi', $date);
                    }
                } else {
                    if ($showday) {
                        return String::init(date('l, F jS' . $y . ' H\hi', $date));
                    } else {
                        return String::init(date('F jS' . $y . ' H\hi', $date));
                    }
                }
            }
        }
    }
}
