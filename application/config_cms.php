<?php
define('PATH_ROOT', $root);

//deprecated: 0ld core still used in CMS3
if (!defined('PATH_CORE')) {define('PATH_CORE', PATH_ROOT . '../php.utf8/');}

#define('PATH_CORE_OOP', PATH_CORE . 'oop4/');

define('PATH_C', $root . 'application/controllers/');
define('PATH_C_CMS', PATH_C . 'cms/');
define('PATH_CMS', PATH_CORE . 'cms3/');

$config = $config_project;

ini_set('error_log', $root . 'logs/' . $config['site']['url'] . '-php.log');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
