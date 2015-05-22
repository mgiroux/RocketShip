<?php

use RocketShip\Controller\Application;
use RocketShip\AssetTypes;

class AjaxController extends Application
{
    public function dispatcher($url)
    {
        /* Render to JSON (ajax, duh!) */
        $this->view->render(null, AssetTypes::JSON);
    }
}
