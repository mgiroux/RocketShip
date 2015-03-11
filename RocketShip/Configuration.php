<?php

namespace RocketShip;

use Symfony\Component\Yaml\Yaml;
use String;
use Collection;
use Number;

class Configuration
{
    /* Applications configurations and settings */
    private static $application_definition;
    private static $application_configurations;
    private static $application_constants;
    private static $application_database;
    private static $application_locales;
    private static $configuration_cache;

    /**
     *
     * get the whole requested configuration object
     *
     * @param   string  configuration object name
     * @return  mixed   the configuration object or null (if name is not valid)
     * @access  public
     * @static
     *
     */
    public static function getObject($type)
    {
        $type = (string)$type;

        switch ($type)
        {
            case 'configuration':
                $conf = self::$application_configurations;
                break;

            case 'constants':
                $conf = self::$application_constants;
                break;

            case 'locales':
                $conf = self::$application_locales;
                break;

            default:
                return null;
                break;
        }

        return $conf;
    }

    /**
     *
     * Get application's configuration or optionally get a certain section of application's configuration
     * The section requested must be using the dot notation. for example : database.mysql.host or database.mysql
     *
     * @param     string     type of data to get (configuration, database or constants)
     * @param     string     section name
     * @return    mixed      null or object of configuration
     * @access    public
     *
     */
    public static function get($type='configuration', $section=null)
    {
        $type    = (string)$type;
        $section = (string)$section;

        switch ($type)
        {
            case 'configuration':
                $conf = self::$application_configurations;
                break;

            case 'database':
                $conf = self::$application_database->database;
                break;

            case 'constants':
                $conf = self::$application_constants;
                break;

            case 'definition':
                $conf = self::$application_definition;
                break;

            default:
                return null;
                break;
        }

        if (!empty($section)) {
            $sections = explode(".", $section);

            foreach ($sections as $num => $key) {
                if (isset($conf->{$key})) {
                    $conf = $conf->{$key};
                } else {
                    return null;
                }
            }

            return $conf;
        } else {
            return null;
        }
    }

    /**
     *
     * Load current application's constants, configuration and database files
     *
     * @access  public
     * @static
     *
     */
    public static function loadAppConfigurations()
    {
        $path = dirname(__DIR__) . '/app/configurations';

        /* Application database settings */
        self::$application_definition = self::parseArrayToObject(Yaml::parse($path . '/application.yaml'));

        /* Application configuration */
        self::$application_configurations = self::parseArrayToObject(Yaml::parse($path . '/configuration.yaml'));

        /* Application constants */
        self::$application_constants = self::parseArrayToObject(Yaml::parse($path . '/constants.yaml'));

        /* Application constants */
        self::$application_locales = self::parseArrayToObject(Yaml::parse($path . '/locale.yaml'));

        /* Application database settings */
        self::$application_database = self::parseArrayToObject(Yaml::parse($path . '/database.yaml'));
    }

    /**
     *
     * Load a configuration file and parse it (the file is cache for upcoming request)
     *
     * @param   string  full path to the file you want to load
     * @return  mixed   object with the configuration, null if file not found
     * @access  public
     *
     */
    public static function loadFile($file)
    {
        $file      = (string)$file;
        $file_hash = md5($file);

        if (isset(self::$configuration_cache[$file_hash]) && !empty(self::$configuration_cache[$file_hash])) {
            return self::$configuration_cache[$file_hash];
        }

        if (file_exists($file)) {
            $configuration_data = self::parseArrayToObject(Yaml::parse($file));

            self::$configuration_cache[$file_hash] = $configuration_data;

            return $configuration_data;
        } else {
            return null;
        }
    }

    /**
     *
     * Parse an array to cast it into an object recursively
     *
     * @param   mixed   the array to cast into object
     * @return  object  object representation of the array
     * @access  public
     * @static
     *
     */
    public static function parseArrayToObject($array)
    {
        $array = ($array instanceof Collection) ? $array->raw() : $array;

        if ($array != null AND (array_keys($array) === range(0, count($array) - 1))) {
            return $array;
        }

        $object = (object)$array;

        foreach ($object as $key => $value) {
            if (is_array($value)) {
                if (!empty($object->{$key})) {
                    if (!empty($value[0])) {
                        $object->{$key} = Collection::init($value);
                    } else {
                        $object->{$key} = self::parseArrayToObject($value);
                    }
                }
            } else {
                if (!empty($object->{$key})) {
                    if (is_string($value)) {
                        $value = String::init($value);
                    } elseif (is_numeric($value)) {
                        $value = Number::init($value);
                    }

                    $object->{$key} = $value;
                }
            }
        }

        return $object;
    }
}
