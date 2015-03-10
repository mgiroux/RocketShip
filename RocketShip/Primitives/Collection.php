<?php

use RocketShip\Base;

class Collection implements Iterator, Serializable
{
    private $primitiveValue;
    private $loopPosition  = 0;
    private $assocIndex    = 0;
    private $isAssociative = false;

    public function __construct($value)
    {
        if (!is_array($value)) {
            if (is_string($value)) {
                $value = new String($value);
            } elseif (is_numeric($value)) {
                $value = new Number($value);
            }

            $this->primitiveValue = [$value];
        } else {
            foreach ($value as $key => $val) {
                if (is_string($val)) {
                    $val = new String($val);
                } elseif (is_numeric($val)) {
                    $val = new Number($val);
                } elseif (is_array($val)) {
                    $val = new Collection($val);
                } elseif ($val instanceof stdClass) {
                    $val = Base::toPrimitive($val);
                }

                if (is_string($key)) {
                    $this->isAssociative = true;
                }

                $value[$key] = $val;
            }

            $this->primitiveValue = $value;
        }
    }

    public static function init($value)
    {
        return new Collection($value);
    }

    public function __get($key)
    {
        return $this->primitiveValue[$key];
    }

    public function __set($key, $value)
    {
        $this->primitiveValue[$key] = $value;
    }

    public function set($key, $value) {
        $this->primitiveValue[$key] = $value;
    }

    /**
     *
     * Append to the collection
     *
     * @param   mixed       element to add
     * @return  Collection  this object
     * @access  public
     *
     */
    public function append($element)
    {
        $element = Base::toPrimitive($element);

        $this->primitiveValue[] = $element;
        return $this;
    }

    /**
     *
     * Append to the collection with given key
     *
     * @param   mixed       element to add
     * @param   mixed       string object or php string for the key
     * @return  Collection  this object
     * @access  public
     *
     */
    public function appendWithKey($element, $key)
    {
        $element = Base::toPrimitive($element);

        $this->primitiveValue[$key] = $element;
        return $this;
    }

    /**
     *
     * Prepend to the collection
     *
     * @param   mixed       element to add
     * @return  Collection  this object
     * @access  public
     *
     */
    public function prepend($element)
    {
        $element = Base::toPrimitive($element);

        array_unshift($this->primitiveValue, $element);
        return $this;
    }

    /**
     *
     * Remove from the collection
     *
     * @param   mixed       index to remove (number, int, string object or php string)
     * @return  Collection  this object
     * @access  public
     *
     */
    public function remove($index)
    {
        $index = Base::toRaw($index);

        unset($this->primitiveValue[$index]);
        return $this;
    }

    /**
     *
     * get random parts of the collection
     *
     * @param   mixed       number or int for length
     * @return  Collection  this object
     * @access  public
     *
     */
    public function random($count)
    {
        $count = Base::toRaw($count);
        $keys  = array_rand($this->primitiveValue, $count);
        $out   = [];

        foreach ($keys as $key) {
            $out[] = $this->primitiveValue[$key];
        }

        return new Collection($out);
    }

    /**
     *
     * Shuffle the collection
     *
     * @return  Collection  this object
     * @access  public
     *
     */
    public function shuffle()
    {
        shuffle($this->primitiveValue);
        return $this;
    }

    /**
     *
     * Reverse the collection
     *
     * @param   bool        preserve keys?
     * @return  Collection  this object
     * @access  public
     *
     */
    public function reverse($preserve=false)
    {
        $this->primitiveValue = array_reverse($this->primitiveValue, $preserve);
        return $this;
    }

    /**
     *
     * replace elements in the collection
     *
     * @param   mixed       collection or array with what to replace
     * @return  Collection  this object
     * @access  public
     *
     */
    public function replace($elements)
    {
        $elements = Base::toRaw($elements);

        $this->primitiveValue = new Collection(array_replace($this->raw(), $elements));
        return $this;
    }

    /**
     *
     * Get a part of the collection
     *
     * @param   mixed       number or int for the offset
     * @param   mixed       number or int for the length
     * @param   bool        preserve keys?
     * @return  Collection  this object
     * @access  public
     *
     */
    public function slice($offset, $length, $preserve=false)
    {
        $offset = Base::toRaw($offset);
        $length = Base::toRaw($length);

        return new Collection(array_slice($this->raw(), $offset, $length, $preserve));
    }

    /**
     *
     * Search the collection
     *
     * @param   mixed   element to use as needle
     * @return  mixed   the key for the needle (number or string object)
     * @access  public
     *
     */
    public function search($needle)
    {
        $needle = Base::toRaw($needle);
        $key    = $this->recursiveSearch($needle, $this);

        return ($key !== false) ? $key : false;
    }

    /**
     *
     * Does the array contain the given element
     *
     * @param   mixed   element to look for
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function contains($element)
    {
        $element = Base::toRaw($element);
        return (in_array($element, $this->raw()));
    }

    /**
     *
     * merge an array/collection with this one
     *
     * @param   mixed       Collection or array to merge
     * @return  Collection  this object
     * @access  public
     *
     */
    public function merge($array)
    {
        $array = Base::toRaw($array);

        if (!empty($array)) {
            $this->primitiveValue = array_merge($this->raw(), $array);
        }

        return $this;
    }

    /**
     *
     * get the collection keys
     *
     * @return  Collection  this object
     * @access  public
     *
     */
    public function keys()
    {
        return new Collection(array_keys($this->raw()));
    }

    /**
     *
     * get collection values
     *
     * @return  Collection  this object
     * @access  public
     *
     */
    public function values()
    {
        return new Collection(array_values($this->raw()));
    }

    /**
     *
     * Remove double entries, make collection values unique
     *
     * @return  Collection  this object
     * @access  public
     *
     */
    public function unique()
    {
        return new Collection(array_unique($this->raw()));
    }

    /**
     *
     * Does the collection have the requested key?
     *
     * @param   mixed   string object or php string to look for
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function hasKey($key)
    {
        $key = Base::toRaw($key);
        return (array_key_exists($key, $this->primitiveValue));
    }

    /**
     *
     * How many items in the collection?
     *
     * @return  Number  the item count
     * @access  public
     *
     */
    public function count()
    {
        return new Number(count($this->primitiveValue));
    }

    /**
     *
     * Sort the collection by key
     *
     * @param   bool        reverse sort?
     * @return  Collection  this object
     * @access  public
     *
     */
    public function sortByKey($reverse=false)
    {
        $raw = $this->raw();

        if ($reverse) {
            krsort($raw);
        } else {
            ksort($raw);
        }

        return new Collection($raw);
    }

    /**
     *
     * sort collection
     *
     * @param   bool        reverse sort?
     * @param   bool        maintain keys?
     * @param   int         the sorting flag to use (defaults to SORT_REGULAR)
     * @return  Collection  this object
     * @access  public
     *
     */
    public function sort($reverse=false, $maintain=false, $flag=SORT_REGULAR)
    {
        $raw = $this->raw();

        if ($reverse) {
            if ($maintain) {
                arsort($raw, $flag);
            } else {
                rsort($raw, $flag);
            }
        } else {
            if ($maintain) {
                asort($raw, $flag);
            } else {
                sort($raw, $flag);
            }
        }

        return new Collection($raw);
    }

    /**
     *
     * Run a custom sort on the collection
     *
     * @param   callable    the function to run
     * @param   mixed       string object or php string to use as key
     * @param   bool        maintain keys?
     * @return  Collection  this object
     * @access  public
     *
     */
    public function customSort(callable $callback, $key, $maintain=false)
    {
        $raw = $this->raw();
        $key = Base::toRaw($key);

        if (is_callable($callback)) {
            if ($maintain) {
                uasort($raw, $callback($key));
            } else {
                usort($raw, $callback($key));
            }
        }

        return new Collection($raw);
    }

    /**
     *
     * Break the collection into sub collections
     *
     * @param   mixed       number or int for the length of each chunk
     * @param   bool        preserve keys?
     * @return  Collection  this object
     * @access  public
     *
     */
    public function chunk($count, $preserve=false)
    {
        $count = Base::toRaw($count);
        $array = array_chunk($this->raw(), $count, $preserve);

        return new Collection($array);
    }

    /**
     *
     * Run a map function on the collection
     *
     * @param   callable    the function to run
     * @return  Collection  this object
     * @access  public
     *
     */
    public function map(callable $callback)
    {
        $raw = array_map($callback, $this->raw());

        return new Collection($raw);
    }

    /**
     *
     * Run a reduce function on the collection
     *
     * @param   callable    the function to run
     * @param   mixed       initial value
     * @return  mixed       the result of the reduce
     * @access  public
     *
     */
    public function reduce(callable $callback, $initial=null)
    {
        $initial = Base::toRaw($initial);
        $reduced = array_reduce($this->raw(), $callback, $initial);

        if (is_string($reduced)) {
            return new String($reduced);
        } elseif (is_numeric($reduced)) {
            return new Number($reduced);
        }

        return $reduced;
    }

    /**
     *
     * Return an object representation of this collection (only for associative collections)
     *
     * @return  stdClass    basic class object
     * @access  public
     *
     */
    public function object()
    {
        $json = json_encode($this->raw());
        return json_decode($json);
    }

    /**
     *
     * Join elements of collection into a string by a given string
     *
     * @param   mixed   string object or php string to join the collection elements
     * @return  String  string object
     * @access  public
     *
     */
    public function join($by)
    {
        $by = Base::toRaw($by);
        return new String(implode($by, $this->raw()));
    }

    /**
     *
     * is the collection empty?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isEmpty()
    {
        if (!empty($this->primitiveValue)) {
            return false;
        }

        return true;
    }

    /**
     *
     * output the value of this object
     *
     * @access  public
     *
     */
    public function debug()
    {
        print_r($this->primitiveValue);
    }

    /**
     *
     * Create a JSON representation of the collection
     *
     *
     */
    public function toJSON()
    {
        return json_encode($this->raw());
    }

    /**
     *
     * return the php primitive value of this object
     *
     * @return  array   the php array
     * @access  public
     *
     */
    public function raw()
    {
        $out = [];

        foreach ($this->primitiveValue as $key => $value) {
            $out[$key] = Base::toRaw($value);
        }

        return $out;
    }

    private function recursiveSearch($needle, $haystack)
    {
        foreach($haystack as $key => $value) {
            $current_key = $key;
            $v           = ($value instanceof String) ? $value->lower()->raw() : $value;
            $v           = ($v instanceof Collection) ? $v->raw() : $v;
            $n           = strtolower($needle);

            if ($n === $v || (is_array($v) && $this->recursiveSearch($n, $v) !== false)) {
                return $current_key;
            }
        }

        return false;
    }

    private function getPrimitive()
    {
        return $this->primitiveValue;
    }

    public function rewind()
    {
        if ($this->isAssociative) {
            $keys = array_keys($this->primitiveValue);

            $this->loopPosition = $keys[0];
            $this->assocIndex   = 0;
        } else {
            $this->loopPosition = 0;
        }
    }

    public function current()
    {
        return $this->primitiveValue[$this->loopPosition];
    }

    public function key()
    {
        return $this->loopPosition;
    }

    public function next()
    {
        if ($this->isAssociative) {
            $keys = array_keys($this->primitiveValue);

            ++$this->assocIndex;
            $this->loopPosition = $keys[$this->assocIndex];
        } else {
            ++$this->loopPosition;
        }
    }

    public function valid()
    {
        return isset($this->primitiveValue[$this->loopPosition]);
    }

    public function serialize()
    {
        return serialize($this->raw());
    }

    public function unserialize($string)
    {
        $instance             = new Collection(unserialize($string));
        $this->primitiveValue = $instance->getPrimitive();

        foreach ($this->primitiveValue as $key => $item) {
            if (is_string($key)) {
                $this->isAssociative = true;
                break;
            }
        }
    }
}