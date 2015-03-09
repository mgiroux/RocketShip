<?php

namespace RocketShip\Controller;

use RocketShip\Routing;
use String;

class Bundle extends Base
{
    public function __construct($custom_view_path=null)
    {
        parent::__construct();

        $this->type = String::init('bundle');
        $this->path = String::init(Routing::$current_path . '/controllers');
        $this->view = new \RocketShip\View;
        $name       = String::init(strtolower(str_replace('Controller', '', get_class($this))));

        $path = (empty($custom_view_path)) ? dirname($this->path) . '/views/' . $name : $custom_view_path;

        if (!stristr($path, '/views')) {
            $path .= '/views';
        }

        $this->view->setPath($path);
        $this->view->setAssetsPath($this->app->site_url . '/public/' .  basename(Routing::$current_path));
    }
}