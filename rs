#!/usr/bin/php
<?php

date_default_timezone_set('America/Montreal');

require_once dirname(__DIR__) . '/RocketShip/Application.php';

$app     = new RocketShip\Application;
$console = new RocketShip\Console;

echo "RocketShip CLI v1.2.0 (8)\r\n\r\n";

include_once __DIR__ . '/lib/bin.php';

/* Available options */
$console->addOption(array('model', 'm'), array(
    'default'     => null,
    'description' => 'Generate a model class')
);

$console->addOption(array('controller', 'c'), array(
    'default'     => null,
    'description' => 'Generate a controller class')
);

$console->addOption(array('bundle', 'b'), array(
    'default'     => null,
    'description' => 'Generate a bundle skeleton')
);

$console->addOption(array('directive', 'd'), array(
    'default'     => null,
    'description' => 'Generate a directive skeleton')
);

$console->addOption(array('cli'), array(
    'default'     => null,
    'description' => 'Generate a cli application skeleton')
);

$console->addOption(array('test', 'tdd'), array(
    'default'     => null,
    'description' => 'Generate a test class')
);

$console->addOption(array('target', 't'), array(
    'default'     => 'app',
    'description' => 'leave blank for app or the bundle name')
);

$args = $console->getArguments();

/* Take all arguments and handle what needs to be generated */
$bin = new Bin;
$bin->handleArguments($args);
echo "\r\n";
