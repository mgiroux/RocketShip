<?php

namespace RocketShip\Utils;

/* Shut php up in the terminal */
ini_set('error_reporting', 'off');

class Test extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        /* PHPUnit construct */
        parent::__construct();
        
        /* Windows doesn't like that (bug?) */
        $this->backupGlobals          = false;
        $this->backupStaticAttributes = false;
    }

    /**
     *
     * describe
     *
     * Describe the test class (description + number of tests in test)
     *
     * @param   string  the class name
     * @param   string  the description
     * @access  protected
     * @static
     *
     */
    protected static final function describe($name, $description)
    {
        $methods = get_class_methods($name);
        $count   = 0;

        foreach ($methods as $method) {
            if (strtolower(substr($method, 0, 4)) == 'test') {
                $count++;
            }
        }

        $out       = $description . " ({$count} tests)";
        $chr_count = strlen($out);

        echo $out . "\n";

        $a = 0;
        while ($a < $chr_count) {
            echo '-';
            $a++;
        }

        echo "\n";
    }
}
