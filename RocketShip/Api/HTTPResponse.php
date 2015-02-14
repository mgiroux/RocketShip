<?php

namespace RocketShip\Api;

abstract class HTTPResponse
{
    /**
     * Constant representing response state
     */
    const OK                 = 200;
    const CREATED            = 201;
    const ACCEPTED           = 202;
    const NON_AUTHORITATIVE  = 203;
    const NO_CONTENT         = 204;
    const RESET_CONTENT      = 205;
    const MULTIPLE_CHOICES   = 300;
    const MOVE_PERMANENTLY   = 301;
    const MOVE_TEMPORARILY   = 302;
    const SEE_OTHER          = 303;
    const NOT_MODIFIED       = 304;
    const BAD_REQUEST        = 400;
    const UNAUTHORIZED       = 401;
    const PAYMENT_REQUIRED   = 402;
    const FORBIDDEN          = 403;
    const NOT_FOUND          = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE     = 406;
    const TIMEOUT            = 408;

    /**
     * HTTP Response code mapping
     */
    private $response_codes = [
        200 => 'OK',
        201	=> 'Created',
        202	=> 'Accepted',
        203	=> 'Non-Authoritative',
        204	=> 'No Content',
        205	=> 'Reset Content',
        300	=> 'Multiple Choices',
        301	=> 'Moved Permanently',
        302	=> 'Moved Temporarily',
        303	=> 'See Other',
        304	=> 'Not Modified',
        400 => 'Bad Request',
        401	=> 'Unauthorized',
        402	=> 'Payment Required',
        403	=> 'Forbidden',
        404	=> 'Not Found',
        405	=> 'Method Not Allowed',
        406	=> 'Not Acceptable',
        408	=> 'Request Time-out'
    ];

    public $status;
    public $code;
    public $message;
    public $result;

    /**
     * construct
     */
    public function __construct($state)
    {
        $this->status   = $this->response_codes[$state];
        $this->code     = $state;
        $this->message  = '';
        $this->result  = '';
    }

    /**
     * render
     *
     * Render the response (state, message, results) to a specific format.
     * The format is handled by subclass of HTTP Response.
     *
     * @param string $message The message of the response
     * @param bool   $compress_output If we compress the response output
     * @return void
     */
    abstract public function render($message = '', $compress_output = false);
}
