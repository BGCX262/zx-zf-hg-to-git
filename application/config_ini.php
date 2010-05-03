<?php
if (!empty($_GET['pi5x'])) {phpinfo();die;}
define('T0', microtime(1));
define('UID', uniqid(getmypid()));

#echo 'DEBUG:<br><textarea rows=10 cols=100>' . print_r(get_include_path(), 1) . '</textarea><br>'; die;
#echo 'DEBUG:<br><textarea rows=10 cols=100>' . print_r(getcwd(), 1) . '</textarea><br>'; die;
set_include_path($root . 'library'); // for early Zend_Logger loading

error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('html_errors', 0);

//--< setup location and debug
if (!defined('LOCATION'))
{
	if ( ($_SERVER['SERVER_ADDR'] == '192.168.0.118') || (strpos($_SERVER['SERVER_NAME'], '.lh') !== false) ) { // devel local
		define('LOCATION', 'devel');
		define('LOCALHOST', 1);
	} elseif ( strpos($_SERVER['SERVER_NAME'], 'test') !== false ) { // devel server
		define('LOCATION', 'devel');
		define('LOCALHOST', 0);
	} else {
		define('LOCATION', 'stable');
		define('LOCALHOST', 0);
	}
}
//-->

function __autoload($path) {include str_replace('_','/',$path) . '.php'; return $path;} // $autoloader->setDefaultAutoloader(create_function('$class', "include str_replace('_', '/', \$class) . '.php';"));