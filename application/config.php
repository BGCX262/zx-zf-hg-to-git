<?php
if (!defined('LOCATION')) {echo 'Framework not initialized, exit!'; die;};

if (LOCATION == 'stable')
{
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);

	$config_project['debug']['on'] = false;

	define('APPLICATION_ENV', 'production');

	$debug = 0;
	// forced debug only
	if ( !empty($config_project['site']['stage']) && $config_project['site']['stage'] != 'production' ) { // production can't be debuggable :)
		if (!empty($_GET['zd']) && (is_numeric($_GET['zd']))) { // 'zd' for 'zend debug'
			$debug = $_GET['zd'];
		}
	}
} else {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	$config_project['debug']['on'] = true;

	define('APPLICATION_ENV', 'development');
	if (isset($_GET['zd'])) {
		$debug = $_GET['zd'];
	} else {
		$debug = LOCALHOST;
	}
}

define('ZX_DEBUG', $debug);
#echo 'DEBUG:<br><textarea rows=10 cols=100>' . print_r(ZX_DEBUG, 1) . '</textarea><br>';die;

#if (!defined('CMS')) {

define('ZX_LOG', 1); // todo!

if ( ZX_LOG )
{
	$config_project['logger']['writer'] = $root . 'logs/' . $config_project['site']['url'] . '-zf.log';
	#$config_project['logger']['firebug'] = true; // use l() function for it!

	// NB! force full debug for non-stable location!
	if (empty($config_project['logger']['priority'])) {
		$config_project['logger']['priority'] = (LOCATION != 'stable') ? Zend_Log::DEBUG : Zend_Log::INFO;
	}

	#echo ini_get('include_path') . '<br/>';die; // /srv/sites/php/__PROJECT__/library
	$res = _initLogger($config_project);
	#echo 'DEBUG:<br><textarea rows=10 cols=100>' . print_r($res, 1) . '</textarea><br>'; die;
}

//--< setup cache
#$pathCache = defined('stable.edition') ? '/mnt/storage/aif_st/cache/' : sys_get_temp_dir();

if (!empty($config_project['cache']))
{
$frontendOptions = array(
	'default_options' => array(
		'cache_with_get_variables' => true,
		'cache_with_post_variables' => false,
		'cache_with_session_variables' => false,
		'cache_with_files_variables' => true,
		'cache_with_cookie_variables' => true,
		'make_id_with_get_variables' => false,
		'make_id_with_post_variables' => false,
		'make_id_with_session_variables' => false,
		'make_id_with_files_variables' => false,
		'make_id_with_cookie_variables' => false,
	),
	'lifetime' => 600,
	'debug_header' => !(LOCATION == 'stable'),
	'logging' => ZX_LOG,
	'logger' => ZX_LOG ? Zend_Registry::get('logger') : null,
	'regexps' => array(
		'^/' => array('cache' => true),
		'^/getcaptcha' => array('cache' => false),
	)
);

$backendHtmlOptions = $backendOptions = array(
	'file_name_prefix' => $config_project['cache']['file_name_prefix'],
	'cache_dir' => isset($config_project['cache']['cache_dir']) ? $config_project['cache']['cache_dir'] : sys_get_temp_dir(),#$root . 'cache'
);

$cachePage = Zend_Cache::factory('Zx_Cache_Frontend_Page', 'File', $frontendOptions, $backendOptions, true);

// clear cache!
if ( LOCATION != 'stable' )
{
	if (isset($_GET['no_cache'])) {
		#Zend_Registry::set('remove_cache', true);//TODO!
		$cachePage->setOption('caching', false);
		#$cacheFile->setOption('caching', false);
	}

	if (isset($_GET['clear_cache'])) {
		$cachePage->clean(Zend_Cache::CLEANING_MODE_ALL);
		#$cacheFile->clean(Zend_Cache::CLEANING_MODE_ALL);
	} elseif (isset($_GET['clear_old_cache'])) {
		$cachePage->clean(Zend_Cache::CLEANING_MODE_OLD);
		#$cacheFile->clean(Zend_Cache::CLEANING_MODE_OLD);
	}
}

$cachePage->start();
}
//-->

require 'functions.php';
l($_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'], 'START');

if (LOCALHOST)
{
	$config_project['ymaps']['key'] ='';
	#$config_project['db']['params']['profiler'] = true;
}

if (!empty($config_project['timezone'])) {
	date_default_timezone_set($config_project['timezone']);
} else {
	date_default_timezone_set('Europe/Moscow');
}

if (empty($config_project['locale'])) {
	$config_project['locale'] = 'ru_RU.UTF8';
	mb_internal_encoding('UTF-8'); // instead of ISO-8859-1!
}
$res = setlocale(LC_ALL, $config_project['locale']); // см. также инициализацию Zend_Locale в Zx_Bootstrap!!!
#$res = setlocale(LC_CTYPE, $config_project['locale']); // см. также инициализацию Zend_Locale в Zx_Bootstrap

// Russian tests!
/*
echo strftime('%A %d %B %Y', time()) . '<br/>'; // works?
echo strUp('это тест!'); #echo mb_strtoupper('это тест!', 'UTF-8') . '<br/>'; // works!
echo strLo('ЭТО ТЕСТ!'); #echo mb_strtolower('ЭТО ТЕСТ!', 'UTF-8') . '<br/>'; // works!
echo strUpFirst('это тест!');
die;
*/

// set LC_TIME etc
if (!empty($config_project['locales'])) {
	foreach ($config_project['locales'] as $k => $v) {
		setlocale($k, $v);
	}
}

//} // non-CMS

// @todo это временный костыль!
#$config_project['translate']['locale'] = substr($config_project['locale'], 0, 5); // http://zendframework.ru/forum/index.php?topic=571.0


//--> TODO: remove constants from code!
define('PATH_ROOT', $root); #realpath

// Zend Framework 1.8
defined('APPLICATION_PATH') || define('APPLICATION_PATH', PATH_ROOT . 'application');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

define('PATH_PUB', $root . 'public/');
//define('PATH_PU', $root . 'public'); // no final slash! see Zx_Db_Table::getImagePV() for example

if (!defined('PATH_FW'))
{
	if (class_exists('Zend_Db')) {
		$blob = true; // Zend framework in one file!
	} else {
		$blob = false;
	}

	// 1.7 way
	if (!empty($config_project['deprecated']['version'])) {
		// Windows / XAMMP patch!
		if (strpos($_SERVER['SERVER_SOFTWARE'], 'Win')) {
			define('PATH_FW', 'd:\srv.ntfs\sites\lib\zf\0ld\library/');
			define('PATH_MY', 'd:\srv.ntfs\sites\php\library/');
		} else {
			define('PATH_FW', '/srv/sites/php/library/0ld/');
			define('PATH_MY', '/srv/sites/php/library/');
		}

	// 1.8 way
	} else {
		// Windows / XAMMP patch!
		if (strpos($_SERVER['SERVER_SOFTWARE'], 'Win')) {
			define('PATH_FW', 'd:\srv.ntfs\sites\lib\zf\stable\library/');
			define('PATH_MY', 'd:\srv.ntfs\sites\php\library/');
		} else {
			define('PATH_FW', '/srv/sites/php/library/');
			define('PATH_MY', '/srv/sites/php/library/');
		}
	}
}

define('PATH_M', PATH_ROOT . 'application/models/');
define('PATH_V', PATH_ROOT . 'application/views/');
define('PATH_C', PATH_ROOT . 'application/controllers/');

// Базовый URL. Если вы хотите положить сайт в отдельную папку а не в корень виртуального хоста, этот параметр необходимо  изменить на /dir_name/
$baseUrl = '/';

$config_general = array (

	'routes' => true,

	// Настройки соединения с БД
/*
    'db' => array (
		'adapter'   => 'MYSQLI',//PDO_MYSQL
		'params'    => array(
			'host'          => 'localhost',
			'profiler'      => false,
		),
	),
*/
    // Настройки URL адресов
    'url'   => array (
         // Базовый URL
         'base'         => $baseUrl,
         // Адрес папки где собраны открытые для доступа извне файлы
         'public'       => $baseUrl . 'public',
         // Адрес папки где лежат графические изображения для дизайна
         'img'          => $baseUrl . 'images',
         // Адрес папки где лежат css файлы
         'css'          => $baseUrl . 'css',
     ),

    // Физические пути
    'path'  => array (
        // Путь к document root
        'root'         => $root,
        // Путь к приложениям
        #'application' => $root . '/application/',
        // Путь к библиотекам
        #'libs'         => $root . 'libs/',
        // Путь к моделям
        'models'       => $root . 'application/models/',
        // Путь к контроллерам
        'controllers'  => $root . 'application/controllers/',
        // Путь к контроллерам
        'controllersCommon' => PATH_MY . 'Zx/application/controllers/',
        // Путь к видам
        'views'        => $root . 'application/views/',
        // Путь к видам
        'viewsCommon' => PATH_MY . 'Zx/application/views/',
        // Путь к layouts
        'layouts'      => $root . 'application/views/layouts/',
        // Путь к системным файлам
        'system'       => $root . 'application/system/',
        // Путь к конфигурационным файлам (since 1.8)
        'configs'     => $root . 'application/configs/',
        // Путь к конфигурационным файлам (deprecated)
        'settings'     => $root . 'application/settings/',
     ),

    // Общие настройки
    'common' => array (
         'charset'      => 'utf-8',// Кодировка сайта
     ),

	// Настройки отладки
    'debug' => array (
         'on' => false,
     ),

 	'plugins' => array(// Плагины
		'Template', // we dont need it after Scienta?
	),

/*
	'translate' => array(
		'locale' => $config_project['locale']
	),
*/
    'support' => array (
		'email' => 'avd@informproject.info',//@todo: 911@informproject.info
		'title' => 'Support informproject.info'
     ),

);

//--< NLS configuration
if (!empty($config_project['load_messages'])) {
	$msg = require PATH_MY . 'Zx/application/translate/msg_' . $config_project['load_messages'] . '.php';
} else {
	$msg = require PATH_MY . 'Zx/application/translate/msg_ru.php';
}
$config_general['msg'] = $msg;
//-->


if (empty($config)) {
	$config = my_array_merge_recursive($config_general, $config_project);
}

if (empty($config['site']['admin']['email'])) {
	$config['site']['admin']['title'] = 'Support (' . $config['site']['url'] . ')';
	$config['site']['admin']['email'] = $config['support']['email'];
}

if (!empty($config['site']['url']) && (!strpos(ini_get('error_log'), '-php.log')) ) {
	ini_set('error_log', $root . 'logs/' . $config['site']['url'] . '-php.log');
}

#$paths = implode(PATH_SEPARATOR, array('.', PATH_FW, PATH_MY, $config['path']['controllers'], $config['path']['models'], $config['path']['system']));
#if (defined('PATH_MY')) {$paths .= PATH_SEPARATOR . PATH_MY;}
if (PATH_FW == PATH_MY) {
	set_include_path(implode(PATH_SEPARATOR, array('.', PATH_FW, $config['path']['controllers'], $config['path']['models'], $config['path']['system'])));
} else {
	set_include_path(implode(PATH_SEPARATOR, array('.', PATH_FW, PATH_MY, $config['path']['controllers'], $config['path']['models'], $config['path']['system'])));
}
#echo ini_get('include_path') . '<br/>';die;
#echo ini_get('error_log') . '<br/>';die;

if ( ZX_DEBUG )
{
	$config['debug']['on'] = true;

	if (!empty($_GET['profiler']) && $_GET['profiler'] == 'fb') {
		$config['debug']['profile'] = 'FB'; // Profiling with Firebug http://framework.zend.com/manual/en/zend.db.profiler.html#zend.db.profiler.profilers.firebug
		$config['db']['params']['profiler'] = true;
	}

	#if ( !empty($_GET['zdb']) )
	#if (!isset($cachePage) || !$cachePage->getOption('caching'))
	#{
		$config['plugin_debugbar'] = true; // Scienta ZF Debug Bar (http://jokke.dk/software/scientadebugbar)
	#}
}

/**
 * Zend_Log setup
 * @param array $config
 */
function _initLogger($config = '')
{
	if ( !empty($config['logger']) )
	{
		$conf = $config['logger'];

		$logger = new Zend_Log();

		if ( !empty($conf['writer']) )
		{
			if (!empty($conf['mode'])) {
				$mode = $conf['mode'];
			} elseif (LOCATION == 'stable') {
				$mode = 'a';
			} else {
				$mode = 'w';
			}

			$writer = new Zend_Log_Writer_Stream($conf['writer'], $mode);
			#if (!ZX_DEBUG) {
				$priority = !empty($conf['priority']) ? $conf['priority'] : Zend_Log::INFO;
				$filter = new Zend_Log_Filter_Priority($priority);
				$writer->addFilter($filter);
			#}
			$logger->addWriter($writer); // we care about log length :)
		}

		if ( !empty($conf['firebug']) ) {
			$logger->addWriter(new Zend_Log_Writer_Firebug());
		}

		Zend_Registry::set('logger', $logger);
		return TRUE;
	}
	return FALSE;
}