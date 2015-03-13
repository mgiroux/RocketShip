<?php

namespace RocketShip\Utils;

use String;
use Number;

class Geocoder
{
    public $success;
    public $message;
    
    public $street_number;
    public $street;
    public $city;
    public $country;
    public $country_code;
    public $postal;
    public $neighborhood;
    public $province_state_code;
    public $province_state;
    public $administrative_area;
    
    /**
     *
     * construct
     *
     * Query Google's API for the given address search
     *
     * @access    public
     *
     */
    public function __construct($search)
    {
        $search = (string)$search;

        $formatted = str_replace(" ", "+", $search);
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $formatted . '&sensor=false';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $geoloc = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->success = false;
        $this->message = String::init('no results for query');

        if ($geoloc->status == 'OK') {
            $this->parseData($geoloc);
            $this->success = true;
            $this->message = String::init('OK');
        }
    }
    
    /**
     *
     * parse_data
     *
     * Parse the data received from the successful query
     *
     * @param     object    google's returned result object
     * @return    void
     * @access    private
     * @final
     *
     */
    private final function parseData($geoloc)
    {
        $data = $geoloc->results[0];
        foreach ($data->address_components as $key => $component) {
            $type = $component->types[0];
            
            switch ($type)
            {
                case "street_number":
                    $this->street_number = Number::init($component->long_name);
                    break;
                    
                /* Street */
                case "route":
                    $this->street = String::init($component->long_name);
                    break;
                    
                case "neighborhood":
                    $this->neighborhood = String::init($component->long_name);
                    break;
                    
                /* City */
                case "locality":
                    $this->city = String::init($component->long_name);
                    break;
                
                /* Province/State */
                case "administrative_area_level_1":
                    $this->province_state      = String::init($component->long_name);
                    $this->province_state_code = String::init($component->short_name);
                    break;
                
                /* Administrative area */
                case "administrative_area_level_2":
                    $this->administrative_area = String::init($component->long_name);
                    break;
                    
                case "country":
                    $this->country      = String::init($component->long_name);
                    $this->country_code = String::init($component->short_name);
                    break;
                    
                case "postal_code":
                    $this->postal = String::init($component->long_name);
                    break;
            }
        }
        
        $this->query     = String::init($data->formatted_address);
        $this->latitude  = Number::init($data->geometry->location->lat);
        $this->longitude = Number::init($data->geometry->location->lng);
    }
}
