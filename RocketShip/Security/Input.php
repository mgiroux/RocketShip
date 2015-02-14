<?php

namespace RocketShip\Security;

use RocketShip\Base;

$GLOBALS['_PUT'] = [];

class Input extends Base
{
    private $post;
    private $get;
    private $put;

    public function __construct()
    {
        parent::__construct();

        $post = $this->post('all', true);
        $get  = $this->get('all', true);
        $put  = $this->put('all', true);

        $this->post = new \stdClass;
        $this->get  = new \stdClass;
        $this->put  = new \stdClass;

        foreach ($post as $key => $value) {
            $this->post->{$key} = $value;
        }

        foreach ($get as $key => $value) {
            $this->get->{$key} = $value;
        }

        foreach ($put as $key => $value) {
            $this->put->{$key} = $value;
        }
    }


    /**
     *
     * Getter
     *
     * @param   string  key
     * @return  mixed   value
     * @access  public
     *
     */
    public function __get($key)
    {
        if (!empty($this->post->{$key})) {
            return $this->post->{$key};
        } elseif (!empty($this->get->{$key})) {
            return $this->get->{$key};
        } elseif (!empty($this->put->{$key})) {
            return $this->put->{$key};
        }

        return null;
    }

    /**
     *
     * get Post element
     *
     * @param    string    target post element
     * @param    bool      clean up the value for security purposes or not
     * @param    string    type of filter
     * @return   mixed     value of target post element
     * @access   public
     * @final
     *
     */
    public final function post($element, $clean=false, $filter=null)
    {
        if ($element == 'all') {
            if ($clean) {
                return $this->cleanUp($_POST, $filter);
            } else {
                return $_POST;
            }
        } else {
            if (!empty($_POST[$element])) {
                if ($clean) {
                    return $this->cleanUp($_POST[$element], $filter);
                } else {
                    return $_POST[$element];
                }
            } else {
                return null;
            }
           }
    }

    /**
     *
     * get Get element
     *
     * @param    string    target Get element
     * @param    bool      clean up the value for security purposes or not
     * @param    string    type of filter
     * @return   mixed     value of target Get element
     * @access   public
     * @final
     *
     */
    public final function get($element, $clean=false, $filter=null)
    {
        if ($element == 'all') {
            if ($clean) {
                return $this->cleanUp($_GET, $filter);
            } else {
                return $_GET;
            }
        } else {
            if (!empty($_GET[$element])) {
                if ($clean) {
                    return $this->cleanUp($_GET[$element], $filter);
                } else {
                    return $_GET[$element];
                }
            } else {
                return null;
            }
        }
    }

    /**
     *
     * get Put element
     *
     * @param    string    target Put element
     * @param    bool      clean up the value for security purposes or not
     * @param    string    type of filter
     * @return   mixed     value of target Get element
     * @access   public
     * @final
     *
     */
    public final function put($element, $clean=false, $filter=null)
    {
        global $_PUT;
        if (empty($GLOBALS['_PUT'])) {
            parse_str(file_get_contents('php://input'), $_PUT);
        }

        if ($element == 'all') {
            if ($clean) {
                return $this->cleanUp($_PUT, $filter);
            } else {
                return $_PUT;
            }
        } else {
            if (!empty($_PUT[$element])) {
                if ($clean) {
                    return $this->cleanUp($_PUT[$element], $filter);
                } else {
                    return $_PUT[$element];
                }
            } else {
                return null;
            }
        }
    }

    /**
     *
     * get Angular post data
     *
     * @param    string    target post element
     * @param    bool      clean up the value for security purposes or not
     * @param    string    type of filter
     * @return   mixed     value of target Get element
     * @access   public
     * @final
     *
     */
    public final function angularPost($element, $clean=false, $filter=null)
    {
        global $_POST;
        if (empty($GLOBALS['_POST'])) {
            $_POST = (array)json_decode(file_get_contents('php://input'));
        }

        if ($element == 'all') {
            if ($clean) {
                return $this->cleanUp($_POST, $filter);
            } else {
                return $_POST;
            }
        } else {
            if (!empty($_POST[$element])) {
                if ($clean) {
                    return $this->cleanUp($_POST[$element], $filter);
                } else {
                    return $_POST[$element];
                }
            } else {
                return null;
            }
        }
    }

    /**
     *
     * Clean the give value up
     *
     * @param    string    string to clean up
     * @param    string    filter to use
     * @return   string    cleaned up string
     * @access   private
     * @final
     *
     */
    private final function cleanUp($string, $filter=null)
    {
        if (is_array($string)) {
            foreach ($string as $num => $item) {
                $string[$num] = $this->cleanUp($item, $filter);
            }
            return $string;
        } else {
            if (is_string($string)) {
                $string = urldecode($string);
                $string = strip_tags($string);

                if (!empty($filter)) {
                    switch ($filter)
                    {
                        case "string":
                            $string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                            break;

                        case "int":
                        case "id":
                            $string = filter_var($string, FILTER_SANITIZE_NUMBER_INT);
                            break;

                        case "float":
                            $string = filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT);
                            break;

                        case "email":
                            $string = filter_var($string, FILTER_SANITIZE_EMAIL);
                            break;

                        case "safe":
                            $string = filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_ENCODE_HIGH);
                            break;
                    }
                }

                $string = str_replace(["SELECT *", "WHERE", "ORDER BY", "LIMIT", "GROUP BY", "COUNT", "SELECT", "DELETE FROM", "UPDATE", "DROP", "ALTER"], "", $string);
                return $string;
            } else {
                return $string;
            }
        }
    }

    /**
     *
     * Generate a CSRF token for a form (with complete html)
     *
     * @return  string  html with token
     * @access  public
     *
     */
    public function csrfToken()
    {
        $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
        $_SESSION['csrf_time'] = time();

        echo '<input type="hidden" name="__csrf" value="' . $_SESSION['csrf_token'] . '" />';
    }

    /**
     *
     * Validate the current posted form to match the csrf token and time
     *
     * @param   int     minimum time to respect to detect "bots" or page reload
     * @param   int     maximum time before discarding the form as "timed out"
     * @return  bool    true = OK, false = CSRF hack attempt
     * @access  public
     *
     */
    public function csrfValidation($minimum=3, $maximum=1200)
    {
        $now = time();

        if (($now - $_SESSION['csrf_time']) <= $minimum) {
            return false;
        }

        if (($_SESSION['csrf_time'] + $maximum) > $now) {
            return false;
        }

        if ( (!empty($_SESSION['csrf_token'])) && (!empty($_POST['__csrf'])) ) {
            if ($_SESSION['csrf_token'] == $_POST['__csrf']) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
