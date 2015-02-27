<?php

namespace RocketShip;

abstract class Directives
{
    public static $registered_directives;
    public static $directives_router;

    abstract public function register();
    abstract public function execute($scope, $directive, $html);

    public static final function parse($scope, $layout, $view)
    {
        $exp  = '~<!--(\s?)(.*?)(\s?)-->~msi';
        $out  = '';

        preg_match_all($exp, $layout, $matches);

        /* Run the view directive first */
        foreach ($matches[2] as $num => $match) {
            $directive = strtolower(trim($match));

            if ($directive == 'view') {
                $output = self::$registered_directives->view->execute($scope, $directive, [$view]);
                $out    = str_replace($matches[0][$num], $output, $layout);
                break;
            }
        }

        if (empty($out)) {
            if (!empty($layout)) {
                $out = $layout;
            } else {
                $out = $view;
            }
        }

        /* Look for directives again (the html data changed) */
        preg_match_all($exp, $out, $matches);

        /* Go through all other directives */
        foreach ($matches[2] as $num => $match) {
            if (strpos($match, ':') != false) {
                $sections  = explode(':', $match);
                $directive = strtolower(trim($sections[0]));
                unset($sections[0]);

                $args = array_values($sections);

                /* Parse view variables */
                foreach ($args as $argnum => $value) {
                    $value = trim($value);

                    if (substr($value, 0, 1) == '#') {
                        /* Scope variable */
                        $val = substr($value, 1);
                        $args[$argnum] = $scope->{$val};
                    } elseif (substr($value, 0, 1) == '$') {
                        if (strpos($value, ';')) {
                            $parts = explode(";", $value);
                            $value = $parts[0];

                            if ($value == '$scope') {
                                $value = '';
                            }
                        }
                        ob_start();
                        eval(' echo ' . $value . ';');
                        $args[$argnum] = ob_get_clean();
                    } else {
                        $args[$argnum] = $value;
                    }
                }
            } else {
                $args = [];
                $directive = strtolower(trim($match));
            }

            if (!empty(self::$directives_router->{$directive})) {
                $handler  = self::$directives_router->{$directive};
                $instance = self::$registered_directives->{$handler};
                $return = $instance->execute($scope, $directive, $args);
                $out = str_replace($matches[0][$num], $return, $out);
            }
        }

        return $out;
    }

    public static final function loadAll()
    {
        self::$registered_directives = new \stdClass;
        self::$directives_router     = new \stdClass;

        $app_directives = glob(dirname(__DIR__) . '/app/directives/*.php');
        $sys_directives = glob(__DIR__ . '/Directives/*.php');

        $files = array_merge($sys_directives, $app_directives);

        foreach ($files as $file) {
            self::loadOne($file);
        }
    }

    public static final function loadOne($file)
    {
        include_once $file;

        $name = str_replace('.php', '', basename($file));

        if (class_exists($name)) {
            $instance = new $name;

            if (is_subclass_of($instance, 'RocketShip\Directives')) {
                /* Run the registration method */
                $directive_name = strtolower($name);
                $directives     = $instance->register();

                if (is_array($directives)) {
                    foreach ($directives as $directive) {
                        self::$directives_router->{$directive} = $directive_name;
                    }

                    self::$registered_directives->{$directive_name} = $instance;
                } else {
                    throw new \Exception("The directive named '{$name}' does not return an array of directive names when registering");
                }
            } else {
                throw new \Exception("Directive found that does not extend the Directives base class.");
            }
        } else {
            throw new \Exception("Directive found but the filename does not match the class it implements.");
        }
    }

    private static final function traverse($value, $index)
    {

    }
}
