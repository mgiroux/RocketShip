<?php

namespace RocketShip;

class Base
{
    /**
     *
     * Application static instance
     * @var \RocketShip\Application
     *
     */
    protected $app;

    public function __construct()
    {
        $this->app = Application::$instance;
    }
    
    /**
     *
     * Remove an element from an array and return the new array, reset the array if requested
     *
     * @param   array   the array to edit
     * @param   mixed   the key to remove
     * @param   bool    reset the array (more for numerical arrays) (optional)
     * @return  array
     * @access  protected
     * @final
     *
     */
    final protected function removeFromArray($array, $index, $reset=true)
    {
        unset($array[$index]);

        if ($reset) {
            return array_values($array);
        }

        return $array;
    }
}
