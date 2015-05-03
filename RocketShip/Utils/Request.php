<?php

namespace RocketShip\Utils;

use RocketShip\Api\HTTPResponse;
use RocketShip\Configuration;

class Request
{
    /* Request Type */
    const AJAX     = 'ajax';
    const STANDARD = 'standard';

    /* Request METHOD */
    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';

    /* Protocol */
    const HTTP  = 'http';
    const HTTPS = 'https';

    private $status_code;
    private $request_type;
    private $request_method;
    private $protocol;
    private $url;
    private $client;
    private $ip;
    private $mobile;
    private $platform;

    public function __construct()
    {
        /* Status code */
        if (function_exists("http_response_code")) {
            $this->status_code = http_response_code();
        } else {
            if (!empty($_SERVER['REDIRECT_STATUS'])) {
                $this->status_code = $_SERVER['REDIRECT_STATUS'];
            } else {
                $this->status_code = HTTPResponse::OK;
            }
        }

        /* Request type */
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->request_type = self::AJAX;
        } else {
            $this->request_type = self::STANDARD;
        }

        /* Request method (post, get, etc.) */
        $this->request_method = $_SERVER['REQUEST_METHOD'];

        /* Protocol (IIS support) */
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $this->protocol = 'https';
        } else {
            if ($_SERVER['SERVER_PORT'] == '443') {
                $this->protocol = self::HTTPS;
            } else {
                $this->protocol = self::HTTP;
            }
        }

        /* Client */
        $this->client   = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $this->ip       = $_SERVER['REMOTE_ADDR'];
        $this->url      = $_SERVER['REQUEST_URI'];
        $this->mobile   = $this->isMobile();
        $this->platform = $this->getPlatform();
    }

    public function __get($variable)
    {
        return $this->{$variable};
    }


    /**
     *
     * Return whether the request is an ajax request or not
     *
     * @return  bool    true/false
     * @access  public
     *
     */
    public function ajax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Check if user is on a mobile device
     *
     * @return  bool    true/false
     * @access  public
     *
     */
    public function isMobile()
    {
        $detect      = new \Mobile_Detect;
        $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        if ($device_type == 'tablet' || $device_type == 'phone') {
            return true;
        }

        return false;
    }

    /**
     *
     * Check if browser is one of the 3 iOS devices
     *
     * @return  bool    true/false
     * @access  public
     *
     */
    public function isIOS()
    {
        if (stristr($_SERVER['HTTP_USER_AGENT'], 'iPod') || stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || stristr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Get the user's platform
     *
     * @return  string  tablet, phone or computer
     * @access  public
     *
     */
    public function getPlatform()
    {
        $detect = new \Mobile_Detect;
        return ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    }

    /**
     *
     * Set the HTTP response code for this request
     *
     * @param   int     http code
     * @return  void
     * @access  public
     *
     */
    public function setCode($code)
    {
        $code = ($code instanceof Number) ? $code->raw() : $code;
        http_response_code($code);
    }
}
