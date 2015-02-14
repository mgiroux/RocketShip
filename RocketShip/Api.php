<?php

namespace RocketShip;

use OAuth2\Response;
use OAuth2\Scope;
use OAuth2\Server;
use OAuth2\Storage\Mongo;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\RefreshToken;
use OAuth2\Request;
use OAuth2\Storage\Memory;
use RocketShip\Api\JSONHTTPResponse;
use RocketShip\Api\HTTPResponse;

class Api extends Base
{
    private $server;
    private $mongo;

    public function __construct()
    {
        parent::__construct();

        $config = Configuration::get('database', $this->app->environment);

        if (!empty($config->user) && !empty($config->password)) {
            $mongo = new \MongoClient("mongodb://{$config->user}:{$config->password}@{$config->host}:{$config->port}/{$config->database}");
        } else {
            $mongo = new \MongoClient("mongodb://{$config->host}:{$config->port}/{$config->database}");
        }

        $this->mongo  = $mongo->{$config->database};
        $storage      = new Mongo($this->mongo);
        $this->server = new Server($storage, ['allow_implicit' => true]);

        $this->server->addGrantType(new ClientCredentials($storage));

        /* Possible scopes */
        $default_scope    = 'default';
        $available_scopes = Configuration::get('definition', 'api.scopes');
        $memory           = new Memory(['default_scope' => $default_scope, 'supported_scopes' => $available_scopes]);
        $scope_util       = new Scope($memory);
        $this->server->setScopeUtil($scope_util);
    }

    /**
     *
     * Authorize the user (token request)
     *
     * @param   string  the current uri
     * @access  public
     *
     */
    public function authenticate($uri)
    {
        if ($uri == '/oauth2/authorize' || $uri == '/oauth2/authorize/') {
            echo $this->server->handleTokenRequest(Request::createFromGlobals())->send();
            $this->app->quit();
        }
    }

    /**
     *
     * Validate the token is valid before moving on
     * Exits on failure
     *
     * @access  public
     *
     */
    public function validateToken()
    {
        if (!$this->server->verifyResourceRequest(Request::createFromGlobals())) {
            $this->sendUnauthorized(null);
            $this->app->quit();
        }
    }

    /**
     *
     * Validate the verb used for the request
     * Exits on failure
     *
     * @param   array   list of verbs allowed
     * @access  public
     *
     */
    public function validateVerb($allowed)
    {
        $verb = $_SERVER['REQUEST_METHOD'];

        if (count($allowed) == 1 && $allowed[0] == '*') {
            $allowed = ['GET', 'POST', 'PUT', 'DELETE'];
        }

        if (!in_array($verb, $allowed)) {
            $this->sendBadRequest('You are not allowed to use the ' . $verb . ' method on this endpoint.');
            $this->app->quit();
        }
    }

    /**
     *
     * Verify that client has the right permissions (scope)
     *
     * @param   string  the permission scope
     * @access  public
     *
     */
    public function validatePermission($permission)
    {
        $request = Request::createFromGlobals();
        $response = new Response;

        if (!$this->server->verifyResourceRequest($request, $response, $permission)) {
            $response->send();
            $this->app->quit();
        }
    }

    /**
     *
     * Send a response to the client
     *
     * @param   mixed           the data to send back (array or object)
     * @param   HTTPResponse    the response to send back (http code)
     * @access  public
     *
     */
    public function respond($data, $response=HTTPResponse::OK)
    {
        $out = new JsonHTTPResponse($response);
        $out->result = $data;
        $out->render("OK", false);
        $this->app->quit();
    }

    public function createAccess($redirect_uri, $permissions=['default'])
    {
        $account                = new \stdClass;
        $account->client_id     = uniqid();
        $account->client_secret = md5($account->client_id . $this->app->config->general->hash_salt);
        $account->redirect_uri  = $redirect_uri;
        $account->scope         = implode(',', $permissions);
        $this->mongo->selectCollection('oauth_clients')->insert($account, ['w' => 1]);

        $output         = new \stdClass;
        $output->key    = $account->client_id;
        $output->secret = $account->client_secret;
        return $output;
    }

    /**
     *
     * Send an unauthorized response to the client
     *
     * @param   string  message to send
     * @access  private
     *
     */
    private function sendUnauthorized($msg=null)
    {
        if (empty($msg)) {
            $msg = 'Authentication token is missing or invalid.';
        }

        $out = new JsonHTTPResponse(HTTPResponse::UNAUTHORIZED);
        $out->render($msg);
    }

    /**
     *
     * Send an bad request response to the client
     *
     * @param   string  message to send
     * @access  private
     *
     */
    private function sendBadRequest($msg=null)
    {
        if (empty($msg)) {
            $msg = 'Bad request.';
        }

        $out = new JsonHTTPResponse(HTTPResponse::BAD_REQUEST);
        $out->render($msg);
    }
}
