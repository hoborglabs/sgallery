<?php
error_reporting(0);
$loader = include __DIR__ . '/vendors/autoload.php';
$loader->add('Hoborg', __DIR__ . '/');
defined('SG_VERSION') || define('SG_VERSION', 'unknown');

use Hoborg\SGallery\Application;

$console = new Application('Simple Gallery', SG_VERSION);
$console->init();
$console->setApplicationRoot(__DIR__);
$console->run();
