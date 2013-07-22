#!/usr/bin/env php

<?php

define(APP_VERSION, '0.1b');

$loader = require_once __DIR__ . '/app/bootstrap.php';

// Parse commandline options
$resolver = new \MainBundle\Console\OptionResolver();
$resolver->run();

// TODO: use buffered output
ob_implicit_flush(true);

$controller = new \MainBundle\Controller\MainController();
$container  = \MainBundle\Container\ServiceContainer::getInstance();
$stream     = $container->get('stream');

while(true) :

    if($stream->isEnd()) :

        echo sprintf('(%s) END OF STREAM', date('Y-m-d H:i:s')); //@todo use logger
        break;
    endif;

    if($input = $stream->read()) :

        $controller->handleAction($input);
        $controller->cycleAction();

        continue;
    endif;

    $controller->cycleAction();
endwhile;