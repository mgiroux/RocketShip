<?php

namespace RocketShip\Utils;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

class Http
{
    private $client;
    private $request;
    private $url;
    private $uri;
    private $auth;
    private $_user;
    private $_password;
    public $response;
    public $request_failed;

    public function __construct($url)
    {
        /* Find URI in url */
        $pos       = strpos($url, '/', 7);
        $this->uri = substr($url, $pos);
        $this->url = substr($url, 0, $pos);

        /* Assumme / if the url does not have a uri */
        if (empty($this->uri)) {
            $this->uri = '/';
        }

        $this->client = new Client($url);
    }

    /**
     *
     * authentication
     *
     * Set authentication data for the request
     *
     * @param   string  username
     * @param   string  password
     * @return  object  this object
     * @access  public
     *
     */
    public function authentication($user, $password)
    {
        $this->_user     = $user;
        $this->_password = $password;

        return $this;
    }

    /**
     *
     * setURI
     *
     * Set the target URI for your request
     * Note: this should only be the resource (Ex: /my-page/) and not the full url
     *
     * @param   string  uri for the request
     * @return  object  this object
     * @access  public
     *
     */
    public function setURI($uri)
    {
        $this->uri($uri);
        return $this;
    }

    /**
     *
     * send
     *
     * Send the request and get the result
     *
     * @return  object  this object
     * @access  public
     *
     */
    public function send()
    {
        if (!empty($this->request)) {
            /* Authentication if already set */
            if (!empty($this->_user) && !empty($this->_password)) {
                $this->request->setAuth($this->_user, $this->_passowrd);
            }

            try {
                $this->response       = $this->request->send();
                $this->request_failed = false;
            } catch (ClientErrorResponseException $e) {
                $response             = $e->getResponse();
                $msg                  = $response->getMessage();
                $parts                = explode("\r\n\r\n", $msg);
                $this->response       = $parts[1];
                $this->request_failed = true;
            }
        }

        return $this;
    }

    /**
     *
     * get
     *
     * Execute a GET request
     *
     * @param   array   optional headers
     * @param   string  just-in-time uri change
     * @return  object  this object
     * @access  public
     *
     */
    public function get($headers=null, $uri=null)
    {
        /* JIT uri change */
        if (!empty($uri)) {
            $this->uri = $uri;
        }

        $this->request = $this->client->get($this->uri, $headers);
        return $this;
    }

    /**
     *
     * post
     *
     * Execute a POST request
     *
     * @param   array   fields to post, for files add @ before your file's path (ex: => "@/my/path/file")
     * @param   array   optional headers
     * @param   string  just-in-time uri change
     * @return  object  this object
     * @access  public
     *
     */
    public function post($fields=[], $headers=null, $uri=null)
    {
        /* JIT uri change */
        if (!empty($uri)) {
            $this->uri = $uri;
        }

        $this->request = $this->client->post($this->uri, $headers, $fields);
        return $this;
    }

    /**
     *
     * put
     *
     * Execute a PUT request
     *
     * @param   string  data to send with the request
     * @param   array   optional headers
     * @param   string  just-in-time uri change
     * @return  object  this object
     * @access  public
     *
     */
    public function put($data='', $headers=null, $uri=null)
    {
        /* JIT uri change */
        if (!empty($uri)) {
            $this->uri = $uri;
        }

        $this->request = $this->client->put($this->uri, $headers, $data);
        return $this;
    }

    /**
     *
     * delete
     *
     * Execute a DELETE request
     *
     * @param   string  data to send with the request
     * @param   array   optional headers
     * @param   string  just-in-time uri change
     * @return  object  this object
     * @access  public
     *
     */
    public function delete($data='', $headers=null, $uri=null)
    {
        /* JIT uri change */
        if (!empty($uri)) {
            $this->uri = $uri;
        }

        $this->request = $this->client->delete($this->uri, $headers, $data);
        return $this;
    }

    /**
     *
     * head
     *
     * Execute a HEAD request
     *
     * @param   array   optional headers
     * @param   string  just-in-time uri change
     * @return  object  this object
     * @access  public
     *
     */
    public function head($headers=null, $uri=null)
    {
        /* JIT uri change */
        if (!empty($uri)) {
            $this->uri = $uri;
        }

        $this->request = $this->client->head($this->uri, $headers);
        return $this;
    }

    /**
     *
     * patch
     *
     * Execute a PATCH request
     *
     * @param   string  data to send with the request
     * @param   array   optional headers
     * @param   string  just-in-time uri change
     * @return  object  this object
     * @access  public
     *
     */
    public function patch($data='', $headers=null, $uri=null)
    {
        /* JIT uri change */
        if (!empty($uri)) {
            $this->uri = $uri;
        }

        $this->request = $this->client->patch($this->uri, $headers, $data);
        return $this;
    }

    /**
     *
     * options
     *
     * Execute a OPTIONS request
     *
     * @param   array   optional headers
     * @param   string  just-in-time uri change
     * @return  object  this object
     * @access  public
     *
     */
    public function options($headers=null, $uri=null)
    {
        /* JIT uri change */
        if (!empty($uri)) {
            $this->uri = $uri;
        }

        $this->request = $this->client->options($this->uri, $headers);
        return $this;
    }

    /**
     *
     * setDefaultHeaders
     *
     * Set the default headers to add to all instance requests
     *
     * @param   array   header to push in
     * @return  object  this object
     * @access  public
     *
     */
    public function setDefaultHeaders($headers=null)
    {
        $this->client->setDefaultOption('headers', $headers);
        return $this;
    }

    /**
     *
     * getHeader
     *
     * Get requested header value
     *
     * @param   string  header name
     * @return  string  header value
     * @access  public
     *
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    /**
     *
     * getResponse
     *
     * Get the request's reponse
     *
     * @return  string  the request reponse body
     * @access  public
     *
     */
    public function getResponse()
    {
        if (is_object($this->response)) {
            return $this->response->getBody(true);
        } else {
            return $this->response;
        }
    }
}
