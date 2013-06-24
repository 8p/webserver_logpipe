<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors',  1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

// You can search the include_path as a last resort.
$loader->useIncludePath(true);

// Register bundles
$loader->registerNamespaces(array(
    'MainBundle'    => sprintf('%s/../src', __DIR__),
    'LogfileBundle' => sprintf('%s/../src', __DIR__),
    'ZabbixBundle'  => sprintf('%s/../src', __DIR__)
));

$loader->register();

return $loader;