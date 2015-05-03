<?php

namespace RocketShip;

class Base
{
    /**
     *
     * Application static instance
     * @var \RocketShip\Application
     *
     */
    protected $app;

    public function __construct()
    {
        $this->app = Application::$instance;
    }
}