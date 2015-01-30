<?php

namespace RocketShip;

use cli\Arguments;
use cli\Tree;
use cli\tree\Ascii;

class Console
{
    private $_arguments;

    public function __construct()
    {
        if (php_sapi_name() != 'cli') {
            die('Must run from command line');
        }

        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 1);
        ini_set('log_errors', 0);
        ini_set('html_errors', 0);

        $tz = @date_default_timezone_get();

        if ($tz == 'UTC') {
            date_default_timezone_set('America/Montreal');
        }

        $this->_arguments = new Arguments(compact('strict'));
        $this->_arguments->addFlag(array('help', 'h'), 'Show this help screen');
    }

    /**
     *
     * Add an option to the CLI script
     *
     * @param   array   option flags (ex: help, h)
     * @param   array   description, default setting
     * @access  public
     *
     */
    public function addOption($options, $settings)
    {
        $this->_arguments->addOption($options, $settings);
    }

    /**
     *
     * Add a flag to the CLI script
     *
     * @param   array   option flags (ex: help, h)
     * @param   string  description
     * @access  public
     *
     */
    public function addFlag($flag, $description)
    {
        $this->_arguments->addFlag($flag, $description);
    }

    /**
     *
     * Get the passed arguments to the CLI script
     *
     * if --help (-h) is passed, help menu is displayed and the CLI dies
     *
     * @return  array   list of arguments and options passed (along with their data)
     * @access  public
     *
     */
    public function getArguments()
    {
        $this->_arguments->parse();

        if ($this->_arguments['help']) {
            echo $this->_arguments->getHelpScreen();
            echo "\n\n";
            die();
        } else {
            $data = json_decode($this->_arguments->asJSON());
            return $data;
        }
    }

    /**
     *
     * Display a tree of information (normally a filesystem type thing)
     *
     * @param   array   list of things to display
     * @access  public
     *
     */
    public function displayTree($list)
    {
        $tree = new tree;
        $tree->setData($list);
        $tree->setRenderer(new Ascii);
        $tree->display();
    }

    /**
     *
     * Write out to the terminal/console
     *
     * @param   string  the text to display (supports {:var} parsing)
     * @param   array   list of variables used
     * @access  public
     *
     */
    public function write($text, $variables=[])
    {
        \cli\Colors::enable();
        \cli\out($text, $variables);
    }

    /**
     *
     * Display an error message (red background, white text)
     * @param   string  the text to display (supports {:var} parsing)
     * @param   array   list of variables used
     *
     */
    public function error($text, $variables=[])
    {
        \cli\Colors::enable();
        \cli\err('%1%W' . $text . '%n', $variables);
    }
}
