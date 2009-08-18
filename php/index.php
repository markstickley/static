<?php
$dir = dirname(__FILE__);
$parentDir = substr($dir,0,strrpos($dir, '/'));
include($parentDir.'/conf/config.php');
header('Location: '.WEBROOT);