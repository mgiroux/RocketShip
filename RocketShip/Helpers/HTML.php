<?php

namespace RocketShip\Helpers;

use RocketShip\Base;

class HTML extends Base
{
    /**
     *
     * Load css files (each argument is 1 file) (outputs the css html code directly)
     *
     * @param     string     infinite list of files (ex: css('file1', 'file2', 'file3'))
     * @return    void
     * @access    protected
     * @final
     *
     */
    public final function css()
    {
        $files = func_get_args();
        $path  = str_replace($this->app->site_url . '/', '', 'public/app') . '/css';

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

                    if (file_exists($this->app->root_path . '/' . $path . '/' . $file)) {
                        /* Add file modification time only for developement and staging environments (helps with debugging) */
                        if ($this->app->config->development->anticaching == 'yes') {
                            $time = filemtime($path . '/' . $file);
                            echo '<link rel="stylesheet" href="' . $this->app->site_url . '/' . $path . '/' . $file . '?' . $time . '">' . "\n";
                        } else {
                            echo '<link rel="stylesheet" href="' . $this->app->site_url . '/' . $path . '/' . $file . '">' . "\n";
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
     * @return  void
     * @access  public
     * @final
     *
     */
    public final function bundlecss()
    {
        $args   = func_get_args();
        $bundle = $args[0];
        unset($args[0]);

        $path  = 'public/' . $bundle . '/css';

        foreach ($args as $file) {
            if (!strstr($file, '.css')) {
                if (!stristr($file, '.htc')) {
                    /* Add .css if not added (lazy loading) */
                    $file .= '.css';
                }
            }

            if ($this->app->config->development->anticaching == 'yes') {
                /* Add file modification time only for developement and staging environments (helps with debugging) */
                if (\RocketShip\Configuration::get('configuration', 'development.anticaching') == 'yes') {
                    $time = filemtime($path . '/' . $file);
                    echo '<link rel="stylesheet" href="' . $this->app->site_url . '/' . $path . '/' . $file . '?' . $time . '">' . "\n";
                } else {
                    echo '<link rel="stylesheet" href="' . $this->app->site_url . '/' . $path . '/' . $file . '">' . "\n";
                }
            }
        }
    }

    /**
     *
     * Load javascript files (each argument is 1 file) (outputs the javascript html code directly)
     *
     * @param     string     infinite list of files (ex: javascript('file1', 'file2', 'file3'))
     * @return    void
     * @access    protected
     * @final
     *
     */
    public final function js()
    {
        $files = func_get_args();
        $path  = str_replace(SITE_URL . '/', '', 'public/app') . '/javascript';

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

                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $path . '/' . $file)) {
                        /* Add file modification time only for developement and staging environments (helps with debugging) */
                        if ($this->app->config->development->anticaching == 'yes') {
                            $time = filemtime($path . '/' . $file);
                            echo '<script type="text/javascript" src="' . $this->app->site_url . '/' . $path . '/' . $file . '?' . $time . '"></script>' . "\n";
                        } else {
                            echo '<script type="text/javascript" src="' . $this->app->site_url . '/' . $path . '/' . $file . '"></script>' . "\n";
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
     * @return  void
     * @access  public
     * @final
     *
     */
    public final function bundlejs()
    {
        $args   = func_get_args();
        $bundle = $args[0];
        unset($args[0]);

        $path  = 'public/' . $bundle . '/javascript';

        foreach ($args as $file) {
            /* Handle special CDNs for jquery, jquery-ui, bootstrap, angular */
            switch ($file)
            {
                case "jquery":
                    echo '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>' . "\n";
                    break;

                case "jquery-ui":
                    echo '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>' . "\n";
                    break;

                case "bootstrap":
                    echo '<script type="text/javascript" src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>' . "\n";
                    break;

                case "angular":
                    echo '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1/angular.min.js"></script>' . "\n";
                    break;

                default:
                    if (!strstr($file, '.js')) {
                        /* Add .js if not added (lazy loading) */
                        $file .= '.js';
                    }

                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $path . '/' . $file)) {
                        /* Add file modification time only for developement and staging environments (helps with debugging) */
                        if ($this->app->config->development->anticaching == 'yes') {
                            $time = filemtime($path . '/' . $file);
                            echo '<script type="text/javascript" src="' . $this->app->site_url . '/' . $path . '/' . $file . '?' . $time . '"></script>' . "\n";
                        } else {
                            echo '<script type="text/javascript" src="' . $this->app->site_url . '/' . $path . '/' . $file . '"></script>' . "\n";
                        }
                    }
                    break;
            }
        }
    }

    /**
     *
     * Load an image from the views public images path (outputs directly)
     *
     * @param     string     image source
     * @return    void
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
     * Get a fullp
     *
     */
    public final function fromPublicPath($file)
    {
        echo $this->app->site_url . '/public/app/' . $file;
    }
}
