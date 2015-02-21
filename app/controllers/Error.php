<?php

use RocketShip\Controller\Application;
use RocketShip\Api\HTTPResponse;

class ErrorController extends Application
{
    public function notFound()
    {
        $this->app->request->setCode(HTTPResponse::NOT_FOUND);
    }

    public function forbidden()
    {
        $this->app->request->setCode(HTTPResponse::FORBIDDEN);
    }
}