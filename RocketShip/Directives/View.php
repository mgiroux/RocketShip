<?php

use RocketShip\Directives;
use RocketShip\AssetTypes;
use RocketShip\Helpers\HTML;

class View extends Directives
{
    private $scope;

    public function register()
    {
        return ['view', 'inject', 'include'];
    }

    public function execute($scope, $directive, $arguments)
    {
        $this->scope = $scope;

        switch ($directive)
        {
            case 'view':
                return $arguments[0];
                break;

            case 'include':
                ob_start();
                $this->scope->partial($arguments[0], null);
                return ob_get_clean();
                break;

            case 'inject':
                if (strtolower($arguments[0]) == AssetTypes::JS) {
                    return HTML::$injected_js;
                } else {
                    return HTML::$injected_css;
                }
                break;
        }
    }

    /**
     *
     * Get a value for the given key
     *
     * @param   string  the key
     * @return  mixed   the value if it exists
     * @access  public
     *
     */
    public function __get($key)
    {
        if ($key != 'rendered') {
            if (!empty($this->scope->{$key})) {
                return $this->scope->{$key};
            }
        } else {
            return $this->scope->rendered;
        }

        return null;
    }
}