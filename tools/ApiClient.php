<?php

class ApiClient
{
    public $expires;
    public $token;
    public $authorize_url;
    public $host;
    public $key;
    public $secret;

    private $token_filename;

    public function __construct($token_filename=null)
    {
        if (empty($token_filename)) {
            $token_filename = $_SERVER['DOCUMENT_ROOT'] . '/api_credentials.token';
        }

        $this->token_filename = $token_filename;
        $this->expires        = 0;

        if (file_exists($this->token_filename)) {
            $data = file_get_contents($this->token_filename);

            if (!empty($data)) {
                $data = json_decode($data);

                $this->expires = abs($data->creation_time - time());
                $this->token   = $data->access_token;
            } else {
                $this->expires = 0;
            }
        }
    }

    public function call($resource, $method='get', $data=array())
    {
        $refresh = false;

        if ($this->expires <= 60) {
            $refresh = true;
        }

        if ($refresh) {
            if (!empty($this->authorize_url)) {

                $ch = curl_init($this->authorize_url);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);
                $response = json_decode(curl_exec($ch));

                $response->creation_time = time();
                $this->token             = $response->access_token;

                file_put_contents($this->token_filename, json_encode($response));
            } else {
                throw new \RuntimeException("ApiClient: Authorization url not provided");
                exit();
            }
        }

        if (!empty($this->token)) {
            $query_string = http_build_query($data);
            $headers      = array('Authorization: Bearer ' . $this->token);

            switch (strtolower($method))
            {
                case 'get':
                    $query_string = (!empty($query_string)) ? '?' . $query_string : $query_string;
                    $ch           = curl_init($this->host . $resource . $query_string);
                    break;

                case 'post':
                    $ch = curl_init($this->host . $resource);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
                    break;

                case 'put':
                    $fp = fopen('php://temp/maxmemory:1000000', 'w+');
                    fwrite($fp, $query_string);
                    fseek($fp, 0);

                    $ch = curl_init($this->host . $resource);
                    curl_setopt($ch, CURLOPT_PUT, true);
                    curl_setopt($ch, CURLOPT_INFILE, $fp);
                    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($query_string));
                    break;

                case 'delete':
                    $ch = curl_init($this->host . $resource);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            return json_decode(curl_exec($ch));
        }
    }
}