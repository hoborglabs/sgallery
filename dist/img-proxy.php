<?php
if (empty($_SERVER['PATH_INFO'])) { die('no path_info'); } $fileName = $_SERVER['PATH_INFO']; $config = getConfig(); $fileFullPath = realpath($config['source'] . $fileName); if (empty($fileFullPath)) { die('404 ;('); } if (0 !== strpos($fileFullPath, $config['source'])) { die('Oh snap!'); } header('Content-Type: image/jpeg'); readfile($fileFullPath);
