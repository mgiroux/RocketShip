<?php

namespace RocketShip\Utils;

define('VALID_NOT_EMPTY', '/.+/');
define('VALID_NUMBER', '/^[-+]?\\b[0-9]*\\.?[0-9]+\\b$/');
define('VALID_EMAIL', "/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,4}|museum|travel)$/i");
define('VALID_YEAR', '/^[12][0-9]{3}$/');
define('VALID_IP', '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])');
define('VALID_HOSTNAME', '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)');

class Validation
{
    /**
     *
     * notEmpty
     *
     * Check if the value is not empty or not
     *
     * @param    string    value to check
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function notEmpty($value)
    {
        $regex = '/[^\s]+/m';
        return $this->regex($regex, $value);
    }

    /**
     *
     * personName
     *
     * Check if any invalid person name characters are found
     *
     * @param    string    name
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function personName($value)
    {
        $char_set = str_split($value);
        $bad = array(
            '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+',
            '`', '~', '[', ']', '\'', '\\', ';', '"', '{', '}', '|', '?',
            '/', '<', '>', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            '÷', '≥', '≤', '…æ', '“', '‘', '«', '≠', 'º', 'ª', '•', '¶', '§', '∞', '¢', '£', '™', '¡',
            'œ', '∑', '´', '®', '†', '¥', '¨', 'ˆ', 'ø', 'π', 'å', 'ß', '∂', 'ƒ', '©', '˙', '∆', '˚',
            '¬', 'Ω', '≈', '√', '∫', '˜', 'µ'
        );

        foreach ($char_set as $num => $char) {
            if (in_array($char, $bad)) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * alphanum
     *
     * Check that string is alpha numeric
     *
     * @param    string    value to check
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function alphanum($value)
    {
        $regex = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu';
        return $this->regex($regex, $value);
    }

    /**
     *
     * between
     *
     * Check that value is between minimum and maximum
     *
     * @param    int        value to check
     * @param    int        minimum value
     * @param    int        maximum value
     * @return   bool       true/false
     * @access   public
     * @final
     *
     */
    public final function between($value, $min, $max)
    {
        $length = strlen($value);
        return ($length >= $min && $length <= $max);
    }

    /**
     *
     * creditcard
     *
     * Check if value is a valid credit card number
     *
     * @param    string    credit card number
     * @param    string    type of card to validate against (empty = try all)
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function creditcard($value, $card=null)
    {
        if (empty($card)) {
            $type = 'all';
        }

        $value = str_replace(array('-', ' '), '', $value);

        if (strlen($value) < 13) {
            return false;
        }

        $cards = array('all' => array(
            'amex'       => '/^3[4|7]\\d{13}$/',
            'bankcard'   => '/^56(10\\d\\d|022[1-5])\\d{10}$/',
            'diners'     => '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/',
            'disc'       => '/^(?:6011|650\\d)\\d{12}$/',
            'electron'   => '/^(?:417500|4917\\d{2}|4913\\d{2})\\d{10}$/',
            'enroute'    => '/^2(?:014|149)\\d{11}$/',
            'jcb'        => '/^(3\\d{4}|2100|1800)\\d{11}$/',
            'maestro'    => '/^(?:5020|6\\d{3})\\d{12}$/',
            'mastercard' => '/^5[1-5]\\d{14}$/',
            'solo'       => '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/',
            'switch'     => '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4][0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/',
            'visa'       => '/^4\\d{12}(\\d{3})?$/',
            'voyager'    => '/^8699[0-9]{11}$/'
        ),
            'fast' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/');

        if (empty($type)) {
            if (!empty($cards['all'][strtolower($card)])) {
                $regex = $cards['all'][strtolower($card)];

                if ($this->regex($regex, $value)) {
                    return $this->_luhn($value);
                }   else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            foreach ($cards['all'] as $value) {
                $regex = $value;

                if ($this->regex($regex, $value)) {
                    return $this->_luhn($value);
                }
            }

            return false;
        }
    }

    /**
     *
     * date
     *
     * Check if value is a valid date in the given format
     *
     * @param    string    date to verify
     * @param    string    format to look for
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function date($value, $format="dmy")
    {
        $regex['dmy'] = '%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\/|-|\\.|\\x20)(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\/|-|\\.|\\x20)0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\\d|2[0-8])(\\/|-|\\.|\\x20)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
        $regex['mdy'] = '%^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])(\\/|-|\\.|\\x20)(?:29|30)\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:0?2(\\/|-|\\.|\\x20)29\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:(?:0?[1-9])|(?:1[0-2]))(\\/|-|\\.|\\x20)(?:0?[1-9]|1\\d|2[0-8])\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
        $regex['ymd'] = '%^(?:(?:(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\\/|-|\\.|\\x20)(?:0?2\\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\\d)?\\d{2})(\\/|-|\\.|\\x20)(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]))))$%';
        $regex['dMy'] = '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ((1[6-9]|[2-9]\\d)\\d{2})$/';
        $regex['Mdy'] = '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sept|Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))))\\,?\\ ((1[6-9]|[2-9]\\d)\\d{2}))$/';
        $regex['My']  = '%^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)[ /]((1[6-9]|[2-9]\\d)\\d{2})$%';
        $regex['my']  = '%^(((0[123456789]|10|11|12)([- /.])(([1][9][0-9][0-9])|([2][0-9][0-9][0-9]))))$%';

        $format = (is_array($format)) ? array_values($format) : array($format);
        foreach ($format as $key) {
            $reg = $regex[$key];

            if ($this->regex($reg, $value) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * email
     *
     * Check that value is a valid email address
     *
     * @param    string    email address
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function email($value)
    {
        $regex = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . VALID_HOSTNAME . '$/i';
        return $this->regex($regex, $value);
    }

    /**
     *
     * ip
     *
     * Check that value is a valid IP address
     *
     * @param    string    value to check\
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function ip($value)
    {
        $regex = '/^' . VALID_IP . '$/';
        return $this->regex($regex, $value);
    }

    /**
     *
     * money
     *
     * Check that value is in money format
     *
     * @param    string    value to check
     * @param    string    where is the dollar sign (left or right)
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function money($value, $symbolPosition="right")
    {
        if ($symbolPosition == 'right') {
            $regex = '/^(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?(?<!\x{00a2})\p{Sc}?$/u';
        } else {
            $regex = '/^(?!\x{00a2})\p{Sc}?(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?$/u';
        }

        return $this->regex($regex, $value);
    }

    /**
     *
     *numeric
     *
     * Check that value is a numeric value
     *
     * @param    string    value to check\
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function numeric($value)
    {
        return is_numeric($value);
    }

    /**
     *
     * phone
     *
     * Check that given value is a valid phone number
     *
     * @param    string    string to check
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    public final function phone($value, $country="all")
    {
        switch ($country)
        {
            case 'us':
            case 'ca':
                $regex = '/^(?:\+?1)?[-. ]?\\(?[2-9][0-8][0-9]\\)?[-. ]?[2-9][0-9]{2}[-. ]?[0-9]{4}$/';
                break;
            default:
                $regex = '/^([0-9-, ()]+)$/';
                break;
        }

        return $this->regex($regex, $value);
    }

    /**
     *
     * postal
     *
     * Check that given value is a postal code (ca, us, gb, it, de, be)
     *
     * @param    string    value to check
     * @param    string    country code (ca is default)
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    function postal($value, $country="ca")
    {
        switch ($country)
        {
            case 'gb':
                $regex = '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]( ?)[0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i';
                break;

            case 'ca':
                $regex = '/\\A\\b[ABCEGHJKLMNPRSTVXY][0-9][A-Z]( ?)[0-9][A-Z][0-9]\\b\\z/i';
                break;

            case 'it':
            case 'de':
                $regex = '/^[0-9]{5}$/i';
                break;

            case 'be':
                $regex = '/^[1-9]{1}[0-9]{3}$/i';
                break;

            case 'us':
            default:
                $regex = '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i';
                break;
        }

        return $this->regex($regex, $value);
    }

    /**
     *
     * url
     *
     * Check that given value is a valid url
     *
     * @param    string    value to check
     * @param    bool      strict or not (default false)
     * @return   bool      true/false
     * @access   public
     * @final
     *
     */
    function url($value, $strict=false)
    {
        $validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=') . '\/0-9a-z]|(%[0-9a-f]{2}))';
        $regex      = '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)' . (!empty($strict) ? '' : '?') .
            '(?:' . VALID_IP . '|' . VALID_HOSTNAME . ')(?::[1-9][0-9]{0,3})?' .
            '(?:\/?|\/' . $validChars . '*)?' .
            '(?:\?' . $validChars . '*)?' .
            '(?:#' . $validChars . '*)?$/i';

        return $this->regex($regex, $value);
    }

    /**
     *
     * @param    string    regex rule
     * @param    string    value to test
     * @return   bool      pass/fail
     * @access   private
     * @final
     *
     */
    private final function regex($regex, $value)
    {
        if (preg_match($regex, $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * _luhn
     *
     * Credit card number validation
     *
     * @param    string    credit card number
     * @return   bool      pass/fail
     * @access   private
     * @final
     *
     */
    private final function _luhn($number)
    {
        $sum = 0;
        $alt = false;

        for($i = strlen($number) - 1; $i >= 0; $i--){
            $n = substr($number, $i, 1);
            if($alt){
                /* square n */
                $n *= 2;
                if($n > 9) {
                    $n = ($n % 10) +1;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }

        return ($sum % 10 == 0);
    }
}
