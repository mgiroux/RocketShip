<?php

use RocketShip\Helpers\Text;
use RocketShip\Security\Encryption;
use RocketShip\Utils\Validation;

class String
{
    const ENCODING_BASE64 = 'base64';
    const ENCODING_URL = 'url';

    const CRYPT_DEFAULT = 'rijndael';
    const CRYPT_BLOWFISH = 'blowfish';
    const CRYPT_TWOFISH = 'twofish';
    const CRYPT_3DES = '3des';
    const CRYPT_AES = 'aes';

    const TRIM_BOTH = 'both';
    const TRIM_LEFT = 'left';
    const TRIM_RIGHT = 'right';

    const HASH_MD5 = 'md5';
    const HASH_CRC32 = 'crc32';
    const HASH_SHA1 = 'sha1';

    const CLEAN_STRING = 'string';
    const CLEAN_INT = 'int';
    const CLEAN_FLOAT = 'float';
    const CLEAN_EMAIL = 'email';
    const CLEAN_SAFE = 'safe';

    const STR_ALL = 'all';

    private $primitiveValue;

    public function __construct($value)
    {
        $value = (string)$value;
        $this->primitiveValue = $value;
    }

    public static function init($value)
    {
        return new String($value);
    }

    /**
     *
     * Return the php string primitive
     *
     * @return  string  the string value
     * @access  public
     *
     */
    public function __toString()
    {
        return $this->primitiveValue;
    }

    /**
     *
     * Generate a unique id string
     *
     * @return  String  this object
     * @access  public
     *
     */
    public static function uniqueID()
    {
        return new String(uniqid());
    }

    /**
     *
     * To lowercase
     *
     * @return  String  this object
     * @access  public
     *
     */
    public function lower()
    {
        if ($this->mbstringAvailable()) {
            return new String(mb_strtolower($this->primitiveValue));
        }

        $this->primitiveValue = new String(strtolower($this->primitiveValue));
    }

    /**
     *
     * To uppercase
     *
     * @return  String  this object
     * @access  public
     *
     */
    public function upper()
    {
        if ($this->mbstringAvailable()) {
            return new String(mb_strtoupper($this->primitiveValue));
        }

        return new String(strtoupper($this->primitiveValue));
    }

    /**
     *
     * Capitalize string
     *
     * @return  String  this object
     * @access  public
     *
     */
    public function capitalize()
    {
        return new String(ucfirst($this->primitiveValue));
    }

    /**
     *
     * Capitalize all words
     *
     * @return  String  this object
     * @access  public
     *
     */
    public function capitalizeAll()
    {
        return new String(ucwords($this->primitiveValue));
    }

    /**
     *
     * Contains the given string?
     *
     * @param   mixed   either a php string or String instance
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function contains($string)
    {
        $string = (string)$string;

        if (stristr($this->primitiveValue, $string)) {
            return true;
        }

        return false;
    }

    /**
     *
     * substring
     *
     * @param   mixed   offset (number or php int)
     * @param   mixed   length (number or php int)
     * @return  String  this object
     * @access  public
     *
     */
    public function substring($offset, $length=null)
    {
        $offset = intval($offset);
        $length = intval($length);

        if ($this->mbstringAvailable()) {
            $sub = mb_substr($this->primitiveValue, $offset, $length);
        } elseif ($this->iconvAvailable()) {
            $sub = iconv_substr($this->primitiveValue, $offset, $length);
        } else {
            $sub = substr($this->primitiveValue, $offset, $length);
        }

        return new String($sub);
    }

    /**
     *
     * truncate string
     *
     * @param   mixed   offset (number or php int)
     * @param   mixed   end the string with the following string (string or php string)
     * @param   bool    preserve word integrity or not
     * @return  String  this object
     * @access  public
     *
     */
    public function truncate($length, $endchar = '...', $preserve = false)
    {
        $length = intval($length);
        $endchar = (string)$endchar;
        $text = new Text;

        return new String($text->truncate($this->primitiveValue, $length, $endchar, $preserve));
    }

    /**
     *
     * encode either with base64 or url encoding
     *
     * @param   string  encoding to use
     * @return  String  this object
     * @access  public
     *
     */
    public function encode($type = self::ENCODING_BASE64)
    {
        if ($type == self::ENCODING_BASE64) {
            return new String(base64_encode($this->primitiveValue));
        }

        return new String(rawurlencode($this->primitiveValue));
    }

    /**
     *
     * decode either with base64 or url encoding
     *
     * @param   string  decoding to use
     * @return  String  this object
     * @access  public
     *
     */
    public function decode($type = self::ENCODING_BASE64)
    {
        if ($type == self::ENCODING_BASE64) {
            return new String(base64_decode($this->primitiveValue));
        }

        return new String(rawurldecode($this->primitiveValue));
    }

    /**
     *
     * Split string with given string piece
     *
     * @param   mixed      string to split with (object or php string)
     * @return  Collection collection object with string objects parts
     * @access  public
     *
     */
    public function split($by)
    {
        $by = (string)$by;
        $elements = explode($by, $this->primitiveValue);

        foreach ($elements as $num => $element) {
            $elements[$num] = new String($element);
        }

        return new Collection($elements);
    }

    /**
     *
     * replace a string or array of strings by other string/strings
     *
     * @param   mixed   string or collection of strings
     * @param   mixed   string or collection of strings
     * @return  String  this object
     * @access  public
     *
     */
    public function replace($target, $replacement)
    {
        if (is_array($target)) {
            foreach ($target as $num => $el) {
                $target[$num] = (string)$el;
            }
        }

        if (is_array($replacement)) {
            foreach ($replacement as $num => $el) {
                $replacement[$num] = (string)$el;
            }
        }

        if (is_object($target)) {
            if ($target instanceof Collection) {
                $target = $target->raw();
            } elseif ($target instanceof String) {
                $target = (string)$target;
            }
        }

        if (is_object($replacement)) {
            if ($replacement instanceof Collection) {
                $replacement = $replacement->raw();
            } elseif ($replacement instanceof String) {
                $replacement = (string)$replacement;
            }
        }

        return new String(str_replace($target, $replacement, $this->primitiveValue));
    }

    /**
     *
     * encrypt the string
     *
     * possible cyphers: CRYPT_DEFAULT, CRYPT_TWOFISH, CRYPT_BLOWFISH, CRYPT_3DES, CRYPT_AES
     *
     * @param   string  the cypher to use, by default rijndael is used
     * @return  String  this object
     * @access  public
     *
     */
    public function encrypt($cypher = self::CRYPT_DEFAULT)
    {
        return new String(Encryption::encrypt($this->primitiveValue, $cypher));
    }

    /**
     *
     * decrypt the string
     *
     * possible cyphers: CRYPT_DEFAULT, CRYPT_TWOFISH, CRYPT_BLOWFISH, CRYPT_3DES, CRYPT_AES
     *
     * @param   string  the cypher to use, by default rijndael is used
     * @return  String  this object
     * @access  public
     *
     */
    public function decrypt($cypher = self::CRYPT_DEFAULT)
    {
        return new String(Encryption::decrypt($this->primitiveValue, $cypher));
    }

    /**
     *
     * append a string to this one
     *
     * @param   mixed   string or php string to append
     * @return  String  this object
     * @access  public
     *
     */
    public function append($string)
    {
        return new String($this->primitiveValue . (string)$string);
    }

    /**
     *
     * prepend a string to this one
     *
     * @param   mixed   string or php string to prepend
     * @return  String  this object
     * @access  public
     *
     */
    public function prepend($string)
    {
        return new String((string)$string . $this->primitiveValue);
    }

    /**
     *
     * Reverse a string (support for multibyte)
     *
     * @return  String  this object
     * @access  public
     *
     */
    public function reverse()
    {
        if ($this->mbstringAvailable()) {
            $encoding = mb_detect_encoding($this->primitiveValue);
            $length = mb_strlen($this->primitiveValue, $encoding);
            $reversed = '';

            while ($length-- > 0) {
                $reversed .= mb_substr($this->primitiveValue, $length, 1, $encoding);
            }

            return new String($reversed);
        }

        return new String(strrev($this->primitiveValue));
    }

    /**
     *
     * Length of the string
     *
     * @return  Number  the length of the string
     * @access  public
     *
     */
    public function length()
    {
        if ($this->mbstringAvailable()) {
            return new Number(mb_strlen($this->primitiveValue));
        } elseif ($this->iconvAvailable()) {
            return new Number(iconv_strlen($this->primitiveValue));
        }

        return new Number(strlen($this->primitiveValue));
    }

    /**
     *
     * wordwrap string
     *
     * @param   mixed   number or int for words per line
     * @param   mixed   end of line character string or php string
     * @return  String  this object
     * @access  public
     *
     */
    public function wordwrap($count, $eol=null)
    {
        $count = intval($count);
        $lines = array_chunk(explode(' ', $this->primitiveValue), $count);
        $final = '';

        if (empty($eol)) {
            $eol = '<br/>';
        } else {
            $eol = (string)$eol;
        }

        foreach ($lines as $word) {
            $final .= implode(' ', $word) . $eol;
        }

        return new String($final);
    }

    /**
     *
     * Turn a string to a safe for url format
     *
     * @return  String this object
     * @access  public
     *
     */
    public function slug()
    {
        $text = new Text;
        return new String($text->slug($this->primitiveValue));
    }

    /**
     *
     * find the location of the given string within this string, reverse is optional
     *
     * @param   mixed   String object or php string
     * @param   bool    reverse or not (defaults to not)
     * @return  String  this object
     * @access  public
     *
     */
    public function indexOf($string, $reverse = false)
    {
        $string = (string)$string;

        if ($this->mbstringAvailable()) {
            if ($reverse) {
                return new Number(mb_strripos($this->primitiveValue, $string));
            }

            return new Number(mb_stripos($this->primitiveValue, $string));
        } elseif ($this->iconvAvailable()) {
            if ($reverse) {
                return new Number(iconv_strrpos($this->primitiveValue, $string));
            }

            return new Number(iconv_strpos($this->primitiveValue, $string));
        }

        if ($reverse) {
            return new Number(strripos($this->primitiveValue, $string));
        }

        return new Number(stripos($this->primitiveValue, $string));
    }

    /**
     *
     * check that string is equal to this string
     *
     * @param   mixed   String object or php string
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function equals($string)
    {
        $string = (string)$string;
        return $this->primitiveValue == $string ? true : false;
    }

    /**
     *
     * trim the string
     *
     * usage: TRIM_BOTH, TRIM_LEFT or TRIM_RIGHT
     *
     * @param   string  type of trim
     * @return  String  this object
     * @access  public
     *
     */
    public function trim($trim = self::TRIM_BOTH)
    {
        if ($trim == self::TRIM_LEFT) {
            return new String(ltrim($this->primitiveValue));
        } elseif ($trim == self::TRIM_RIGHT) {
            return new String(rtrim($this->primitiveValue));
        }

        return new String(trim($this->primitiveValue));
    }

    /**
     *
     * Is the string empty?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isEmpty()
    {
        return (empty($this->primitiveValue)) ? true : false;
    }

    /**
     *
     * hash the string
     *
     * usage: HASH_MD5, HASH_SHA1, HASH_CRC32
     *
     * @param   string  the hash to use
     * @return  String  this object
     * @access  public
     *
     */
    public function hash($type = self::HASH_MD5)
    {
        if ($type == self::HASH_CRC32) {
            return new String(crc32($this->primitiveValue));
        } elseif ($type == self::HASH_SHA1) {
            return new String(sha1($this->primitiveValue));
        }

        return new String(md5($this->primitiveValue));
    }

    /**
     *
     * clean string with requested filter
     *
     * usage: CLEAN_STRING, CLEAN_INT, CLEAN_FLOAT, CLEAN_EMAIL, CLEAN_SAFE
     *
     * @param   string  the type of cleaning filter to apply
     * @return  String  this object
     * @access  public
     *
     */
    public function clean($filter = self::CLEAN_STRING)
    {
        $string = urldecode($this->primitiveValue);
        $string = strip_tags($string);

        if (!empty($filter)) {
            switch ($filter) {
                case self::CLEAN_STRING:
                    $string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                    break;

                case self::CLEAN_INT:
                    $string = filter_var($string, FILTER_SANITIZE_NUMBER_INT);
                    break;

                case self::CLEAN_FLOAT:
                    $string = filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT);
                    break;

                case self::CLEAN_EMAIL:
                    $string = filter_var($string, FILTER_SANITIZE_EMAIL);
                    break;

                case self::CLEAN_SAFE:
                    $string = filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_ENCODE_HIGH);
                    break;
            }
        }

        return new String($string);
    }

    /**
     *
     * Clean string with the safe filter
     *
     * @return  String  this object
     * @access  public
     *
     */
    public function safe()
    {
        $string = urldecode($this->primitiveValue);
        $string = strip_tags($string);
        $string = filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_ENCODE_HIGH);

        return new String($string);
    }

    /**
     *
     * format a string using vsprintf
     *
     * @param   mixed   Collection or array of values
     * @return  String  this object
     * @access  public
     *
     */
    public function format($variables)
    {
        if ($variables instanceof Collection) {
            $variables = $variables->raw();
        }

        return new String(vsprintf($this->primitiveValue, $variables));
    }

    /**
     *
     * run regex against string (preg_match_all)
     *
     * @param   mixed       string object or php string for the pattern
     * @return  Collection  matches
     * @access  public
     *
     */
    public function match($regex)
    {
        $regex = (string)$regex;
        preg_match_all($regex, $this->primitiveValue, $matches);

        return new Collection($matches);
    }

    /**
     *
     * is the string a valid email address?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isEmail()
    {
        $validation = new Validation;
        return $validation->email($this->primitiveValue);
    }

    /**
     *
     * is the string alpha numeric?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isAlphaNum()
    {
        $validation = new Validation;
        return $validation->alphanum($this->primitiveValue);
    }

    /**
     *
     * is the string is a valid date?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isDate()
    {
        $validation = new Validation;
        return $validation->date($this->primitiveValue);
    }

    /**
     *
     * is the string a valid IP?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isIP()
    {
        $validation = new Validation;
        return $validation->ip($this->primitiveValue);
    }

    /**
     *
     * is the string a valid phone number?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isPhone($country = self::STR_ALL)
    {
        $country = (string)$country;
        $validation = new Validation;
        return $validation->phone($this->primitiveValue, $country);
    }

    /**
     *
     * is the string a postal code / zip code ?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isPostal($country = self::STR_ALL)
    {
        $country = (string)$country;
        $validation = new Validation;
        return $validation->postal($this->primitiveValue, $country);
    }

    /**
     *
     * is the string a valid URL string?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isURL()
    {
        $validation = new Validation;
        return $validation->url($this->primitiveValue, true);
    }

    /**
     *
     * return the php primitive value of this object
     *
     * @return  string  the actual php string
     * @access  public
     *
     */
    public function raw()
    {
        return $this->primitiveValue;
    }

    private function mbstringAvailable()
    {
        if (function_exists('mb_strlen')) {
            return true;
        }

        return false;
    }

    private function iconvAvailable()
    {
        if (function_exists('iconv')) {
            return true;
        }

        return false;
    }
}