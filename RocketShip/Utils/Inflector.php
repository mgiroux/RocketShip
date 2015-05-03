<?php

namespace RocketShip\Utils;

class Inflector
{
    protected static $_plural = [
        'rules' => [
             '/(s)tatus$/i' => '\1\2tatuses',
             '/(quiz)$/i' => '\1zes',
             '/^(ox)$/i' => '\1\2en',
             '/([m|l])ouse$/i' => '\1ice',
             '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
             '/(x|ch|ss|sh)$/i' => '\1es',
             '/([^aeiouy]|qu)y$/i' => '\1ies',
             '/(hive)$/i' => '\1s',
             '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
             '/sis$/i' => 'ses',
             '/([ti])um$/i' => '\1a',
             '/(p)erson$/i' => '\1eople',
             '/(m)an$/i' => '\1en',
             '/(c)hild$/i' => '\1hildren',
             '/(buffal|tomat)o$/i' => '\1\2oes',
             '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
             '/us$/i' => 'uses',
             '/(alias)$/i' => '\1es',
             '/(ax|cris|test)is$/i' => '\1es',
             '/s$/' => 's',
             '/^$/' => '',
             '/$/' => 's',
        ],
        'uninflected' => [
             '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'people'
        ],
        'irregular' => [
             'atlas' => 'atlases',
             'beef' => 'beefs',
             'brother' => 'brothers',
             'cafe' => 'cafes',
             'child' => 'children',
             'corpus' => 'corpuses',
             'cow' => 'cows',
             'ganglion' => 'ganglions',
             'genie' => 'genies',
             'genus' => 'genera',
             'graffito' => 'graffiti',
             'hoof' => 'hoofs',
             'loaf' => 'loaves',
             'man' => 'men',
             'money' => 'monies',
             'mongoose' => 'mongooses',
             'move' => 'moves',
             'mythos' => 'mythoi',
             'niche' => 'niches',
             'numen' => 'numina',
             'occiput' => 'occiputs',
             'octopus' => 'octopuses',
             'opus' => 'opuses',
             'ox' => 'oxen',
             'penis' => 'penises',
             'person' => 'people',
             'sex' => 'sexes',
             'soliloquy' => 'soliloquies',
             'testis' => 'testes',
             'trilby' => 'trilbys',
             'turf' => 'turfs',
             'meta' => 'metas',
             'media' => 'medias'
        ]
    ];

    protected static $_singular = [
       'rules' => [
             '/(s)tatuses$/i' => '\1\2tatus',
             '/^(.*)(menu)s$/i' => '\1\2',
             '/(quiz)zes$/i' => '\\1',
             '/(matr)ices$/i' => '\1ix',
             '/(vert|ind)ices$/i' => '\1ex',
             '/^(ox)en/i' => '\1',
             '/(alias)(es)*$/i' => '\1',
             '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
             '/([ftw]ax)es/i' => '\1',
             '/(cris|ax|test)es$/i' => '\1is',
             '/(shoe|slave)s$/i' => '\1',
             '/(o)es$/i' => '\1',
             '/ouses$/' => 'ouse',
             '/([^a])uses$/' => '\1us',
             '/([m|l])ice$/i' => '\1ouse',
             '/(x|ch|ss|sh)es$/i' => '\1',
             '/(m)ovies$/i' => '\1\2ovie',
             '/(s)eries$/i' => '\1\2eries',
             '/([^aeiouy]|qu)ies$/i' => '\1y',
             '/([lr])ves$/i' => '\1f',
             '/(tive)s$/i' => '\1',
             '/(hive)s$/i' => '\1',
             '/(drive)s$/i' => '\1',
             '/([^fo])ves$/i' => '\1fe',
             '/(^analy)ses$/i' => '\1sis',
             '/(analy|ba|diagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
             '/(p)eople$/i' => '\1\2erson',
             '/(m)en$/i' => '\1an',
             '/(c)hildren$/i' => '\1\2hild',
             '/(n)ews$/i' => '\1\2ews',
             '/eaus$/' => 'eau',
             '/^(.*us)$/' => '\\1',
             '/s$/i' => ''
         ],
         'uninflected' => [
             '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss'
         ],
         'irregular' => [
             'foes' => 'foe',
             'waves' => 'wave',
             'curves' => 'curve',
             'metas'  => 'meta'
         ]
    ];

    protected static $_cache = [];
    protected static $_initialState = [];

    protected static $_uninflected = [
         'Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus',
         'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps',
         'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder',
         'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
         'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
         'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', '.*?media',
         'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese',
         'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
         'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors',
         'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'swine', 'testes',
         'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese', 'whiting', 'wildebeest',
         'Yengeese'
    ];

    protected static $_transliteration = [
        '/ä|æ|ǽ/' => 'ae',
         '/ö|œ/' => 'oe',
         '/ü/' => 'ue',
         '/Ä/' => 'Ae',
         '/Ü/' => 'Ue',
         '/Ö/' => 'Oe',
         '/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
         '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
         '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
         '/ç|ć|ĉ|ċ|č/' => 'c',
         '/Ð|Ď|Đ/' => 'D',
         '/ð|ď|đ/' => 'd',
         '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
         '/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
         '/Ĝ|Ğ|Ġ|Ģ/' => 'G',
         '/ĝ|ğ|ġ|ģ/' => 'g',
         '/Ĥ|Ħ/' => 'H',
         '/ĥ|ħ/' => 'h',
         '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
         '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
         '/Ĵ/' => 'J',
         '/ĵ/' => 'j',
         '/Ķ/' => 'K',
         '/ķ/' => 'k',
         '/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
         '/ĺ|ļ|ľ|ŀ|ł/' => 'l',
         '/Ñ|Ń|Ņ|Ň/' => 'N',
         '/ñ|ń|ņ|ň|ŉ/' => 'n',
         '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
         '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
         '/Ŕ|Ŗ|Ř/' => 'R',
         '/ŕ|ŗ|ř/' => 'r',
         '/Ś|Ŝ|Ş|Š/' => 'S',
         '/ś|ŝ|ş|š|ſ/' => 's',
         '/Ţ|Ť|Ŧ/' => 'T',
         '/ţ|ť|ŧ/' => 't',
         '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
         '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
         '/Ý|Ÿ|Ŷ/' => 'Y',
         '/ý|ÿ|ŷ/' => 'y',
         '/Ŵ/' => 'W',
         '/ŵ/' => 'w',
         '/Ź|Ż|Ž/' => 'Z',
         '/ź|ż|ž/' => 'z',
         '/Æ|Ǽ/' => 'AE',
         '/ß/' => 'ss',
         '/Ĳ/' => 'IJ',
         '/ĳ/' => 'ij',
         '/Œ/' => 'OE',
         '/ƒ/' => 'f'
    ];

    /**
     *
     * pluralize
     *
     * Pluralize given word
     *
     * @param     string     the word to pluralize
     * @return    string     plurized word
     * @access    public
     * @static
     *
     */
    public static function pluralize($word)
    {
        if (isset(self::$_cache['pluralize'][$word])) {
            return self::$_cache['pluralize'][$word];
        }

        if (!isset(self::$_plural['merged']['irregular'])) {
            self::$_plural['merged']['irregular'] = self::$_plural['irregular'];
        }

        if (!isset(self::$_plural['merged']['uninflected'])) {
            self::$_plural['merged']['uninflected'] = array_merge(self::$_plural['uninflected'], self::$_uninflected);
        }

        if (!isset(self::$_plural['cacheUninflected']) || !isset(self::$_plural['cacheIrregular'])) {
            self::$_plural['cacheUninflected'] = '(?:' . implode('|', self::$_plural['merged']['uninflected']) . ')';
            self::$_plural['cacheIrregular'] = '(?:' . implode('|', array_keys(self::$_plural['merged']['irregular'])) . ')';
        }

        if (preg_match('/(.*)\\b(' . self::$_plural['cacheIrregular'] . ')$/i', $word, $regs)) {
            self::$_cache['pluralize'][$word] = $regs[1] . substr($word, 0, 1) . substr(self::$_plural['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$_cache['pluralize'][$word];
        }

        if (preg_match('/^(' . self::$_plural['cacheUninflected'] . ')$/i', $word, $regs)) {
            self::$_cache['pluralize'][$word] = $word;
            return $word;
        }

        foreach (self::$_plural['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$_cache['pluralize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$_cache['pluralize'][$word];
            }
        }
    }

    /**
     *
     * singularize
     *
     * Singularize given word
     *
     * @param     string    the word to singularize
     * @return    string    singular word
     * @access    public
     * @static
     *
     */
    public static function singularize($word)
    {
        if (isset(self::$_cache['singularize'][$word])) {
            return String::init(self::$_cache['singularize'][$word]);
        }

        if (!isset(self::$_singular['merged']['uninflected'])) {
            self::$_singular['merged']['uninflected'] = array_merge(
                self::$_singular['uninflected'],
                self::$_uninflected
            );
        }

         if (!isset(self::$_singular['merged']['irregular'])) {
            self::$_singular['merged']['irregular'] = array_merge(
                self::$_singular['irregular'],
                array_flip(self::$_plural['irregular'])
            );
         }

         if (!isset(self::$_singular['cacheUninflected']) || !isset(self::$_singular['cacheIrregular'])) {
            self::$_singular['cacheUninflected'] = '(?:' . join( '|', self::$_singular['merged']['uninflected']) . ')';
            self::$_singular['cacheIrregular'] = '(?:' . join( '|', array_keys(self::$_singular['merged']['irregular'])) . ')';
         }

         if (preg_match('/(.*)\\b(' . self::$_singular['cacheIrregular'] . ')$/i', $word, $regs)) {
             self::$_cache['singularize'][$word] = $regs[1] . substr($word, 0, 1) . substr(self::$_singular['merged']['irregular'][strtolower($regs[2])], 1);
             return self::$_cache['singularize'][$word];
         }

         if (preg_match('/^(' . self::$_singular['cacheUninflected'] . ')$/i', $word, $regs)) {
             self::$_cache['singularize'][$word] = $word;
             return $word;
         }

         foreach (self::$_singular['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$_cache['singularize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$_cache['singularize'][$word];
             }
         }

         self::$_cache['singularize'][$word] = $word;
         return $word;
    }

    /**
     *
     * titleize
     *
     * Convert an underscored or camelcase word into english string
     *
     * @param     string    word to handle
     * @return    string    formatted word
     * @access    public
     * @static
     *
     */
    public static function titleize($word, $uppercase='first')
    {
        $uppercase = $uppercase == 'first' ? 'ucfirst' : 'ucwords';
        return $uppercase(Inflector::humanize(Inflector::underscore($word)));
    }

    /**
     *
     * camelize
     *
     * Camelize given string
     *
     * @param     string    the string to camelize
     * @return    string    camelized string
     * @access    public
     * @static
     *
     */
    public static function camelize($word)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
    }

    /**
     *
     * underscore
     *
     * underscore given string
     *
     * @param     string    the string to underscore
     * @return    string    underscored string
     * @access    public
     * @static
     *
     */
    public static function underscore($word)
    {
        return strtolower(preg_replace('/[^A-Z^a-z^0-9]+/', '_',
            preg_replace('/([a-zd])([A-Z])/', '\1_\2',
            preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $word))
        ));
    }

    /**
     *
     * humanize
     *
     * Humanize given word
     *
     * @param     string    the word to humanize
     * @param     string    type of uppercase
     * @return    string    humanize word
     * @access    public
     * @static
     *
     */
    public static function humanize($word, $uppercase='')
    {
        $uppercase = $uppercase == 'all' ? 'ucwords' : 'ucfirst';
        return $uppercase(str_replace('_', ' ', preg_replace('/_id$/', '', $word)));
    }

    /**
     *
     * tableize
     *
     * Converts a class name to its table name according to rails naming conventions
     *
     * @param     string    class name to get table name
     * @return    string    table name
     * @access    public
     * @static
     *
     */
    public static function tableize($class_name)
    {
        return Inflector::pluralize(Inflector::underscore($class_name));
    }

    /**
     *
     * classify
     *
     * Converts a table name to its class name according to rails naming conventions
     *
     * @param     string    table name
     * @return    string    class name
     * @access    public
     * @static
     *
     */
    public static function classify($table_name)
    {
        return Inflector::camelize(Inflector::singularize($table_name));
    }

    /**
     *
     * ordinalize
     *
     * This method converts 13 to 13th, 2 to 2nd ...
     *
     * @param    int       integer to ordinalize
     * @return   string    formatted string
     * @access   public
     * @static
     *
     */
    public static function ordinalize($number)
    {
        if (in_array(($number % 100), range(11, 13))) {
            return $number . 'th';
        } else {
            switch (($number % 10))
            {
                case 1:
                       return String::init($number . 'st');
                       break;

                case 2:
                    return String::init($number . 'nd');
                    break;

                case 3:
                    return String::init($number . 'rd');
                    break;

                default:
                       return String::init($number . 'th');
                    break;
            }
        }
    }
}