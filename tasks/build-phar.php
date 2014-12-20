<?php

$buildFolder = __DIR__ . '/../phar';
$options = getopt('v:');

$phar = new Phar(__DIR__ . '/../sg.phar', 0, 'sg.phar');
$phar->buildFromDirectory($buildFolder);
$phar->setStub("#!/usr/bin/env php
<?php
Phar::mapPhar('sg.phar');
define('SG_VERSION', '{$options['v']}');
include 'phar://sg.phar/sg.php';
__HALT_COMPILER();
");
