<?php

namespace RocketShip\Helpers;

use RocketShip\Base;

class Text extends Base
{
    /**
     *
     * Format to fit a slug or html filename
     *
     * @param    string   string to remove accents from
     * @param    bool     keep slashes?
     * @return   string   string with no accents
     * @access   public
     *
     */
    public function slug($string, $keep_slashes=false)
    {
        if ($keep_slashes) {
            $string = str_replace("/", "-systemslash-", $string);
        }

        $string = strtolower(trim(preg_replace('~[^0-9a-z.]+~i', '-',
                  html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
                  '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));

        if ($keep_slashes) {
            $string = str_replace("-systemslash-", "/", $string);
        }

        return strtolower($string);
    }

    /**
     *
     * Convert Microsoft quotes to standard stuff for php
     *
     * @param    string    string to parse
     * @return   string    parsed string
     * @access   public
     *
     */
    public function convertSmartQuotes($string)
    {
        $search = array(
            chr(0xe2) . chr(0x80) . chr(0x98),
            chr(0xe2) . chr(0x80) . chr(0x99),
            chr(0xe2) . chr(0x80) . chr(0x9c),
            chr(0xe2) . chr(0x80) . chr(0x9d),
            chr(0xe2) . chr(0x80) . chr(0x93),
            chr(0xe2) . chr(0x80) . chr(0x94)
        );

        $replace = array(
            '&lsquo;',
            '&rsquo;',
            '&ldquo;',
            '&rdquo;',
            '&ndash;',
            '&mdash;'
        );

        return str_replace($search, $replace, $string);
    }

    /**
     *
     * Truncate a phrase to a given number of characters.
     *
     * @param   string    phrase to limit characters of
     * @param   int       number of characters to limit to
     * @param   string    end character or entity
     * @param   boolean   enable or disable the preservation of words while limiting
     * @return  string    truncated string
     * @access  public
     *
     */
    public function truncate($string, $limit=100, $end_char='...', $preserve_words=false)
    {
        $limit = (int)$limit;

        if (function_exists('mb_strlen')) {
            if (trim($string) === '' || mb_strlen($string) <= $limit) {
                return $string;
            }
        } else {
            if (trim($string) === '' || strlen($string) <= $limit) {
                return $string;
            }
        }

        if ($limit <= 0) {
            return $end_char;
        }

        if ($preserve_words === false) {
            return rtrim(substr($string, 0, $limit)) . $end_char;
        }

        if (!preg_match('/^.{0,' . $limit . '}\s/us', $string, $matches)) {
            return $end_char;
        }

        if (function_exists('mb_strlen')) {
            return rtrim($matches[0]) . ((mb_strlen($matches[0]) === mb_strlen($string)) ? '' : $end_char);
        } else {
            return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($string)) ? '' : $end_char);
        }
    }

    /**
     *
     * Replaces the given words with a string.
     *
     * @param   string    phrase to replace words in
     * @param   array     words to replace
     * @param   string    replacement string
     * @param   boolean   replace words across word boundries (space, period, etc)
     * @return  string    censored string
     * @access  public
     *
     */
    public function censor($string, $badwords, $replacement='#', $replace_partial_words=true)
    {
        foreach ((array)$badwords as $key => $badword) {
            $badwords[$key] = str_replace('\*', '\S*?', preg_quote((string)$badword));
        }

        $regex = '(' . implode('|', $badwords) . ')';

        if ($replace_partial_words === false) {
            $regex = '(?<=\b|\s|^)' . $regex . '(?=\b|\s|$)';
        }

        $regex = '!' . $regex . '!ui';

        if (strlen($replacement) == 1) {
            $regex .= 'e';
            return preg_replace($regex, 'str_repeat($replacement, strlen(\'$1\'))', $string);
        }

        return preg_replace($regex, $replacement, $string);
    }
}
