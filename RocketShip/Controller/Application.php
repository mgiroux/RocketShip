<?php

namespace RocketShip\Controller;

class Application extends Base
{
    public function __construct($custom_view_path=null)
    {
        parent::__construct();

        $this->type = 'application';
        $this->path = $this->app->root_path . '/app/controllers';
        $this->view = new \RocketShip\View;
        $name       = strtolower(str_replace('Controller', '', get_class($this)));

        $path = (empty($custom_view_path)) ? dirname($this->path) . '/views/' . $name : $custom_view_path;

        $this->view->setPath($path);
        $this->view->setAssetsPath($this->app->site_url . '/public/app');
    }
}