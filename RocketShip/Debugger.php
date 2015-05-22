<?php

namespace Rocketship;

use DebugBar\StandardDebugBar;
use DebugBar\DataCollector\TimeDataCollector;
use RocketShip\Database\Collection;

class Debugger
{
    private $debugAgent;
    private $debugRenderer;
    private $config;

    public function __construct()
    {
        $this->config = Configuration::getObject('configuration');

        if ($this->isDebugging()) {
            $this->debugAgent    = new StandardDebugBar();
            $this->debugRenderer = $this->debugAgent->getJavascriptRenderer();
        }
    }

    /**
     *
     * Inject the debugger code into the html just before it is sent
     * to the client
     *
     * @param   string  html code to inject in
     * @return  string  the injected html
     * @access  public
     *
     */
    public function injectDebuggerCode($html)
    {
        if ($this->isDebugging()) {
            $total = Collection::getTotalCalls();
            $str   = ($total > 1) ? 'queries' : 'query';

            $this->addMessage('[MongoDB] ' . $total  . ' ' . $str . ' total');

            $debughead   = $this->debugRenderer->renderHead();
            $debugrender = $this->debugRenderer->render();
            $html        = str_replace('</head>', "{$debughead}\n</head>", $html);
            $html        = str_replace('</body>', "{$debugrender}\n</body>", $html);
        }

        return $html;
    }

    /**
     *
     * Start a task to keep time of how long it runs for
     *
     * @param   string  the id for the task
     * @param   string  the message to display
     * @access  public
     *
     */
    public function startTask($id, $message)
    {
        if ($this->isDebugging()) {
            $this->debugAgent['time']->startMeasure($id, $message);
        }
    }

    /**
     *
     * end a started task
     *
     * @param   string  the id of the task to end
     * @access  public
     *
     */
    public function endTask($id)
    {
        if ($this->isDebugging()) {
            $this->debugAgent['time']->stopMeasure($id);
        }
    }

    public function startDBTask($type)
    {
        if ($this->isDebugging()) {
            $app = Application::$instance;
            $id  = 'db_' . $type;

            $stack = debug_backtrace();
            $file  = str_replace($app->root_path, '', $stack[1]['file']);
            $line  = $stack[1]['line'];
            $this->debugAgent['time']->startMeasure($id, '[MongoDB] ' . strtoupper($type) . ' ~> ' . $file . ' on line ' . $line);
        }
    }

    public function endDBTask($type)
    {
        if ($this->isDebugging()) {
            $id  = 'db_' . $type;
            $this->debugAgent['time']->stopMeasure($id);
        }
    }

    /**
     *
     * Add a message in the message tab
     *
     * @param   string  message to add
     * @access  public
     *
     */
    public function addMessage($message)
    {
        if ($this->isDebugging()) {
            $this->debugAgent['messages']->addMessage($message, 'info');
        }
    }

    /**
     *
     * Add a warning in the message tab
     *
     * @param   string  warning to add
     * @access  public
     *
     */
    public function addWarning($message)
    {
        if ($this->isDebugging()) {
            $this->debugAgent['messages']->addMessage($message, 'warning');
        }
    }

    /**
     *
     * Add an error in the message tab
     *
     * @param   string  error to add
     * @access  public
     *
     */
    public function addError($message)
    {
        if ($this->isDebugging()) {
            $this->debugAgent['messages']->addMessage($message, 'error');
        }
    }

    /**
     *
     * Add a custom message in the message tab
     *
     * @param   string  message to add
     * @param   string  type to display (on the right)
     * @access  public
     *
     */
    public function addCustomMessage($message, $type)
    {
        if ($this->isDebugging()) {
            $this->debugAgent['messages']->addMessage($message, strtolower($type));
        }
    }

    /**
     *
     * Add an exception in the exception tab
     *
     * @param   Exception  exception to add
     * @access  public
     *
     */
    public function addException($exception)
    {
        if ($this->isDebugging()) {
            $this->debugAgent['exceptions']->addException($exception);
        }
    }

    private function isDebugging()
    {
        if ($this->config->development->debugging == 'yes') {
            return true;
        }

        return false;
    }
}
