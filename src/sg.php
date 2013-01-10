<?php
$loader = include __DIR__ . '/../vendors/autoload.php';
$loader->add('Hoborg', __DIR__ . '/');

use Hoborg\SGallery\Application;

$console = new Application();
$console->setApplicationRoot(__DIR__ . '/../');
$console->run();