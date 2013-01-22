<?php
// check url
if (empty($_SERVER['PATH_INFO'])) {
	die('no path_info');
}
$fileName = $_SERVER['PATH_INFO'];

// check if file exists and is readable
$config = getConfig();
$fileFullPath = realpath($config['source'] . $fileName);
// security check
if (empty($fileFullPath)) {
	die('404 ;(');
}
if (0 !== strpos($fileFullPath, $config['source'])) {
	die('Oh snap!');
}

// set headers based on file extension
header('Content-Type: image/jpeg');

// proxy file content
readfile($fileFullPath);