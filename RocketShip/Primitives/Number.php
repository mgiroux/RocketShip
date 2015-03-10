<?php

use RocketShip\Base;

class Number
{
    private $primitiveValue;
    private $isInt;

    const PI = M_PI;

    public function __construct($value)
    {
        if (is_integer($value)) {
            $this->isInt = true;
        } else {
            $this->isInt = false;
        }

        $this->primitiveValue = $value;
    }

    public static function init($value)
    {
        return new Number($value);
    }

    /**
     *
     * generate a random number
     *
     * @param   mixed   number or int for minimum value
     * @param   mixed   number or int for maximum value
     * @return  Number  this object
     * @access  public
     * @static
     *
     */
    public static function random($min, $max)
    {
        $min = Base::toRaw($min);
        $max = Base::toRaw($max);

        return new Number(rand(intval($min), intval($max)));
    }

    /**
     *
     * is the number odd?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isOdd()
    {
        return ($this->primitiveValue % 2 == 0) ? false : true;
    }

    /**
     *
     * is the number even?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isEven()
    {
        return ($this->primitiveValue % 2 == 0) ? true : false;
    }

    /**
     *
     * increment the number by 1
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function inc()
    {
        $this->primitiveValue++;
        return $this;
    }

    /**
     *
     * decrement the number by 1
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function dec()
    {
        $this->primitiveValue--;
        return $this;
    }

    /**
     *
     * Add given number
     *
     * @param   mixed   number or int/float to add to this one
     * @return  Number  this object
     * @access  public
     *
     */
    public function sum($number)
    {
        $number = Base::toRaw($number);

        $this->primitiveValue += $number;
        return $this;
    }

    /**
     *
     * Substract given number
     *
     * @param   mixed   number or int/float to substract from this one
     * @return  Number  this object
     * @access  public
     *
     */
    public function sub($number)
    {
        $number = Base::toRaw($number);

        $this->primitiveValue -= $number;
        return $this;
    }

    /**
     *
     * Divide by this number
     *
     * @param   mixed   number or int/float to divide with
     * @return  Number  this object
     * @access  public
     *
     */
    public function divide($number)
    {
        $number = Base::toRaw($number);

        $this->primitiveValue /= $number;
        return $this;
    }

    /**
     *
     * Multiply
     *
     * @param   mixed   number or int/float to multiply with
     * @return  Number  this object
     * @access  public
     *
     */
    public function times($number)
    {
        $number = Base::toRaw($number);

        $this->primitiveValue *= $number;
        return $this;
    }

    /**
     *
     * Modulo
     *
     * @param   mixed   number or int to modulo with
     * @return  Number  this object
     * @access  public
     *
     */
    public function mod($by)
    {
        $by = Base::toRaw($by);
        return new Number($this->primitiveValue % $by);
    }

    /**
     *
     * Float modulo
     *
     * @param   mixed   number or float to modulo with
     * @return  Number  this object
     * @access  public
     *
     */
    public function fmod($by)
    {
        $by = Base::toRaw($by);
        return new Number(fmod($this->primitiveValue, $by));
    }

    /**
     *
     * Round number
     *
     * @param   mixed   number or int to round to (precision)
     * @return  Number  this object
     * @access  public
     *
     */
    public function round($precision)
    {
        if (empty($precision)) {
            $precision = 2;
        }

        $precision = Base::toRaw($precision);

        $this->primitiveValue = round($this->primitiveValue, $precision);
        return $this;
    }

    /**
     *
     * Floor number
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function floor()
    {
        $this->primitiveValue = floor($this->primitiveValue);
        return $this;
    }

    /**
     *
     * Ceil number
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function ceil()
    {
        $this->primitiveValue = ceil($this->primitiveValue);
        return $this;
    }

    /**
     *
     * Get percentage value
     *
     * @param   mixed   number or int/float to calculate percentage with
     * @return  Number  this object
     * @access  public
     *
     */
    public function percentage($total)
    {
        $total = Base::toRaw($total);
        return new Number(round($this->primitiveValue / $total * 100, 2));
    }

    /**
     *
     * Square root
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function sqr()
    {
        return new Number(sqrt($this->primitiveValue));
    }

    /**
     *
     * float value
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function float()
    {
        return new Number(floatval($this->primitiveValue));
    }

    /**
     *
     * Integer value
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function integer()
    {
        return new Number(intval($this->primitiveValue));
    }

    /**
     *
     * absolute value
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function abs()
    {
        return new Number(abs($this->primitiveValue));
    }

    /**
     *
     * is infinite?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isInfinite()
    {
        return is_infinite($this->primitiveValue);
    }

    /**
     *
     * is finite?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isFinite()
    {
        return is_finite($this->primitiveValue);
    }

    /**
     *
     * is not a number?
     *
     * @return  bool    yes/no
     * @access  public
     *
     */
    public function isNan()
    {
        return is_nan($this->primitiveValue);
    }

    /**
     *
     * PI value
     *
     * @return  Number  this object
     * @access  public
     *
     */
    public function pi()
    {
        return new Number(self::PI);
    }

    /**
     *
     * exponential value
     *
     * @param   mixed   number or int to use as power of
     * @return  Number  this object
     * @access  public
     *
     */
    public function pow($exp)
    {
        $exp = Base::toRaw($exp);
        return new Number(pow($this->primitiveValue, $exp));
    }

    /* Aliases */
    public function increment() { return $this->inc(); }
    public function decrement() { return $this->dec(); }
    public function add($number) { return $this->sum($number); }
    public function substract($number) { return $this->sub($number); }
    public function multiply($number) { return $this->times($number); }
    public function modulo($number) { return $this->mod($number); }
    public function fmodulo($number) { return $this->fmod($number); }
    public function squareRoot() { return $this->sqr(); }
    public function sqrRoot() { return $this->sqr(); }

    /**
     *
     * return the php primitive value of this object
     *
     * @return  mixed  the actual integer or float
     * @access  public
     *
     */
    public function raw()
    {
        return $this->primitiveValue;
    }

    public function __toString()
    {
        return (string)$this->primitiveValue;
    }
}