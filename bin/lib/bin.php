<?php

class Bin
{
    public function handleArguments($args)
    {
        /* Default target directory */
        if (empty($args->target)) {
            $args->target = 'default';
        }

        if (isset($args->model)) {
            /* Generate a mongo  model */
            $this->generateMongo($args->model, $args->target);
        }

        if (isset($args->controller)) {
            /* Generate a controller */
            $this->generateController($args->controller, $args->target);
        }

        if (isset($args->bundle)) {
            /* Generate a bundle skeleton */
            $this->generateBundle($args->bundle);
        }

        if (isset($args->directive)) {
            /* Generate a directive */
            $this->generateDirective($args->directive, $args->target);
        }

        if (isset($args->cli)) {
            /* Generate a cli skeleton */
            $this->generateCLI($args->cli);
        }

        if (isset($args->test)) {
            /* Generate a test class */
            $this->generateTest($args->test, $args->target);
        }
    }

    /**
     *
     * generateMongo
     *
     * Generate a mongo model
     *
     */
    private function generateMongo($name, $target)
    {
        $console = new RocketShip\Console;
        $path    = $this->findPath($target);

        if (empty($name)) {
            $console->error("Cannot create model, no name given");
            return;
        }

        if (!empty($path)) {
            $console->write("Generating a model class\n");

            $name     = ucfirst(strtolower($name));
            $filename = $path . '/models/' . $name . '.php';
            $src      = file_get_contents(dirname(__DIR__) . '/templates/model');

            $src = str_replace('[name]', $name, $src);

            if (!file_exists($filename)) {
                file_put_contents($filename, $src);
            }

            if (stristr($path, 'app')) {
                /* Application */
                if (file_exists($filename)) {
                    $pathway = array('app' => array('models' => array($name . '.php <-- Created')));
                    $console->displayTree($pathway);
                } else {
                    $console->error("Cannot create model in application '{:app}'. The model already exists", array('app' => $target));
                }
            } else {
                /* Bundle */
                if (file_exists($filename)) {
                    $pathway = array('bundles' => array($target => array('models' => array($name . '.php <-- Created'))));
                    $console->displayTree($pathway);
                } else {
                    $console->error("Cannot create model in bundle '{:bundle}'. The model already exists", array('bundle' => $target));
                }
            }
        } else {
            $console->error("Cannot find application '{:app}' or bundle '{:bundle}'", array('app' => $target, 'bundle' => $target));
        }
    }

    /**
     *
     * generateController
     *
     * Generate a controller
     *
     */
    private function generateController($name, $target)
    {
        $console = new RocketShip\Console;
        $path    = $this->findPath($target);

        if (empty($name)) {
            $console->error("Cannot create controller, no name given");
            return;
        }

        if (!empty($path)) {
            $console->write("Generating a controller class\n");

            $name     = ucfirst(strtolower($name));
            $filename = $path . '/controllers/' . $name . '.php';
            $src      = file_get_contents(dirname(__DIR__) . '/templates/controller');

            if (stristr($path, 'app')) {
                /* Application */
                $src = str_replace(array('[name]', '[type]'), array($name, 'Application'), $src);

                if (!file_exists($filename)) {
                    file_put_contents($filename, $src);
                }

                if (file_exists($filename)) {
                    $pathway = array('app' =>  array('controllers' => array($name . '.php <-- Created')));
                    $console->displayTree($pathway);
                } else {
                    $console->error("Cannot create controller in application '{:app}'. The model already exists", array('app' => $target));
                }
            } else {
                /* Bundle */
                $src = str_replace(array('[name]', '[type]'), array($name, 'Bundle'), $src);

                if (!file_exists($filename)) {
                    file_put_contents($filename, $src);
                }

                if (file_exists($filename)) {
                    $pathway = array('bundles' => array($target => array('controllers' => array($name . '.php <-- Created'))));
                    $console->displayTree($pathway);
                } else {
                    $console->error("Cannot create controller in bundle '{:bundle}'. The model already exists", array('bundle' => $target));
                }
            }
        } else {
            $console->error("Cannot find application '{:app}' or bundle '{:bundle}'", array('app' => $target, 'bundle' => $target));
        }
    }

    /**
     *
     * generateBundle
     *
     * Generate a bundle and all of it's folders
     *
     */
    private function generateBundle($name)
    {
        $console     = new RocketShip\Console;
        $path        = dirname(dirname(__DIR__)) . '/bundles/';
        $public_path = dirname(dirname(__DIR__)) . '/public/' . strtolower($name);

        if (empty($name)) {
            $console->error("Cannot create bundle, no name given");
            return;
        }

        if (!file_exists($path . strtolower($name))) {
            $console->write("Generating bundle structure\n");

            mkdir($path . strtolower($name));
            @mkdir($public_path);

            $name       = strtolower($name);
            $controller = ucfirst(strtolower($name)) . '.php';
            $class      = ucfirst(strtolower($name));
            $bundle     = ucfirst(strtolower($name)) . 'Bundle.php';

            $dirs = array(
                'configurations', 'controllers', 'helpers', 'locale', 'models',
                'views', 'views/layouts', 'views/' . $name, 'views/partials', 'views/directives'
            );

            $files = array(
                'configurations/configuration.yaml', 'configurations/routes.yaml', 'locale/fr.yaml',
                'locale/en.yaml', 'views/layouts/default.html', 'views/' . $name . '/index.html'
            );

            foreach ($dirs as $dir) {
                @mkdir($path . $name . '/' . $dir);
            }

            foreach ($files as $file) {
                @touch($path . $name . '/' . $file);
            }

            /* Public folders */
            $dirs = array('coffee', 'css', 'less', 'fonts', 'images', 'javascript', 'uploads');

            foreach ($dirs as $dir) {
                @mkdir($public_path . '/' . $dir);
                @touch($public_path . '/' . $dir . '/placeholder');
            }

            /* Controller */
            $src = file_get_contents(dirname(__DIR__) . '/templates/controller');
            $src = str_replace(array('[name]', '[type]'), array($class, 'Bundle'), $src);

            $filename = $path . $name . '/controllers/' . $controller;
            file_put_contents($filename, $src);

            /* Bundle */
            $src = file_get_contents(dirname(__DIR__) . '/templates/bundle');
            $src = str_replace(array('[name]'),
                               array($class), $src);

            $filename = $path . $name . '/' . $bundle;
            file_put_contents($filename, $src);

            if (file_exists($filename)) {
                $pathway = array('bundles' => array($name . ' <-- Created'));
                $console->displayTree($pathway);
            } else {
                $console->error("Cannot create bundle, multiple failures have happened");
            }
        } else {
            $console->error("Cannot create bundle, it already exists");
        }
    }

    /**
     *
     * generateDirective
     *
     * Generate a directive
     *
     */
    private function generateDirective($name, $target)
    {
        $console = new RocketShip\Console;
        $path    = $this->findPath($target);

        if (empty($name)) {
            $console->error("Cannot create directive, no name given");
            return;
        }

        if (!empty($path)) {
            $console->write("Generating a directive class\n");

            $name     = ucfirst(strtolower($name));
            $filename = $path . '/directives/' . $name . '.php';
            $src      = file_get_contents(dirname(__DIR__) . '/templates/directive');

            $src = str_replace('[name]', $name, $src);

            if (!file_exists($filename)) {
                file_put_contents($filename, $src);
            }

            if (stristr($path, 'app')) {
                /* Application */
                if (file_exists($filename)) {
                    $pathway = array('app' => array('directives' => array($name . '.php <-- Created')));
                    $console->displayTree($pathway);
                } else {
                    $console->error("Cannot create directive in app. The directive already exists", array());
                }
            } else {
                /* Bundle */
                if (file_exists($filename)) {
                    $pathway = array('bundles' => array($target => array('directives' => array($name . '.php <-- Created'))));
                    $console->displayTree($pathway);
                } else {
                    $console->error("Cannot create directive in bundle '{:bundle}'. The directive already exists", array('bundle' => $target));
                }
            }
        } else {
            $console->error("Cannot find bundle '{:bundle}'", array('bundle' => $target));
        }
    }
    
    /**
     *
     * generateCLI
     *
     * Generate a CLI application skeleton
     *
     */
    private function generateCLI($name)
    {
        $console = new RocketShip\Console;
        $path    = dirname(dirname(__DIR__)) . '/app/cli/';
        $name    = strtolower($name);

        if (!file_exists($path . $name . '.php')) {
            $console->write("Generating CLI application skeleton\n");
            copy(dirname(__DIR__) . '/templates/cli', $path . $name . '.php');

            $pathway = array('app' => array('cli' => array($name . '.php <-- Created')));
            $console->displayTree($pathway);
        } else {
            $console->error("Cannot create CLI application, it already exists");
        }

    }

    /**
     *
     * generateTest
     *
     * Generate a test class
     *
     */
    private function generateTest($name, $target)
    {
        $console = new RocketShip\Console;
        $path    = dirname(dirname(__DIR__)) . '/app';
        $app     = strtolower($target);
        $name    = strtolower($name);

        if (file_exists($path)) {
            if (!file_exists($path . '/tests/' . ucfirst($name) . 'Test.php')) {
                $console->write("Generating test class\n");

                $src = file_get_contents(dirname(__DIR__) . '/templates/test');
                $src = str_replace(array('[NAME]', '[APP]'), array(ucfirst($name), strtolower($target)), $src);
                file_put_contents($path . '/tests/' . ucfirst($name) . 'Test.php', $src);

                $pathway = array('app' => array($app => array('tests' => array(ucfirst($name) . 'Test.php <-- Created'))));
                $console->displayTree($pathway);
            } else {
                $console->error("Cannot create test, it already exists");
            }
        } else {
            $console->error("Cannot create test, application '{:name}' does not exist", array('name' => strtolower($target)));
        }
    }

    /**
     *
     * findPath
     *
     * Find the path where the target exists
     *
     */
    private function findPath($target)
    {
        $appPath    = dirname(dirname(__DIR__)) . '/app/';
        $bundlePath = dirname(dirname(__DIR__)) . '/bundles/';

        if (file_exists($bundlePath . $target)) {
            return $bundlePath . $target;
        }
        
        return $appPath;
    }

    /**
     *
     * prettyPrint
     *
     * Note: this method exists because "pretty print" option is not available in PHP 5.3 (started at PHP 5.4)
     *
     * Format a JSON to be easily readable
     *
     */
    private function prettyPrint($json)
    {
        $result          = '';
        $level           = 0;
        $prev_char       = '';
        $in_quotes       = false;
        $ends_line_level = NULL;
        $json_length     = strlen( $json );

        for ($i = 0; $i < $json_length; $i++) {
            $char           = $json[$i];
            $new_line_level = NULL;
            $post           = "";

            if ($ends_line_level !== NULL) {
                $new_line_level  = $ends_line_level;
                $ends_line_level = NULL;
            }

            if ($char === '"' && $prev_char != '\\') {
                $in_quotes = !$in_quotes;
            } elseif (!$in_quotes) {
                switch($char)
                {
                    case '}':
                    case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level  = $level;
                    break;

                    case '{':
                    case '[':
                    $level++;

                    case ',':
                    $ends_line_level = $level;
                    break;

                    case ':':
                    $post = " ";
                    break;

                    case " ":
                    case "\t":
                    case "\n":
                    case "\r":
                    $char            = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level  = NULL;
                    break;
                }
            }

            if ($new_line_level !== NULL) {
                $result .= "\n" . str_repeat("\t", $new_line_level);
            }

            $result   .= $char . $post;
            $prev_char = $char;
        }

        return $result;
    }
}
