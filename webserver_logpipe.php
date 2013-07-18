#!/usr/bin/env php

<?php

define(APP_VERSION, '0.1b');

$loader = require_once __DIR__ . '/app/bootstrap.php';

// Parse commandline options
$resolver = new \MainBundle\Console\OptionResolver();
$resolver->run();

// create stream for STDIN
$stdIn = fopen('php://stdin', 'r');

// TODO: use buffered output
ob_implicit_flush(true);

$controller = new \MainBundle\Controller\MainController();

while($line = fgets($stdIn)) :

    $controller->handleAction($line);
endwhile;
