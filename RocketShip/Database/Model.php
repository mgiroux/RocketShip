<?php

namespace RocketShip\Database;

use RocketShip\Database\Collection;

abstract class Model extends Collection
{
    abstract public function init();
}