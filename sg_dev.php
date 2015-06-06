<?php
error_reporting(E_ALL);
$loader = include __DIR__ . '/vendors/autoload.php';
$loader->add('Hoborg', __DIR__ . '/');
define('SG_VERSION', 'development');

use Hoborg\SGallery\Application;

$console = new Application('Simple Gallery', SG_VERSION);
$console->init();
$console->setApplicationRoot(__DIR__);
$console->run();
