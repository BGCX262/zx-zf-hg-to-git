<?php
#require 'Zend/Loader.php';
#require_once 'Zend/Loader/Autoloader.php'; // 1.8
/**
* Bootstrap for ZF (deprecated 1.7-old-school)
* Главный системный класс, используется для настройки и запуска приложения
* @version 8/15/2009
* @todo deprecated!
*/
class Zx_Kernel
{
	/**
	* Запуск приложения
	*/
	public static function run($config)
	{
		try
		{
			##Zend_Loader::registerAutoload(); // pre-1.8
			#$autoloader = Zend_Loader_Autoloader::getInstance(); // 1.8+
			#$autoloader->setFallbackAutoloader(true);

			// Создание объекта конфигурации
			$conf = new Zend_Config($config);

			//--< Zend_Log setup
/*
			if ( !Zend_Registry::isRegistered('logger') && !empty($conf->logger) )
			{
				$logger = new Zend_Log();

				if ( !empty($conf->logger->writer) ) {
					$logger->addWriter(new Zend_Log_Writer_Stream($conf->logger->writer, 'w')); // we care about log length :)
				}

				if ( !empty($conf->logger->firebug) ) {
					$logger->addWriter(new Zend_Log_Writer_Firebug());
				}

				Zend_Registry::set('logger', $logger);
				l($_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . ' START');

				#$filter = new Zend_Log_Filter_Priority(Zend_Log::WARN);
				#$writer->addFilter($filter);
				#$logger = new Zend_Log($writer);
			}
 */
			//-->

			// Занесение объекта конфигурации в реестр
			Zend_Registry::set('conf', $conf);

			// Подключение к базе данных
			self::setDbAdapter();

			// @todo обоснование (пока нужно только для перевода форм, более нигде)
			try {
				$locale = new Zend_Locale($conf->locale);
			} catch (Zend_Locale_Exception $e) {}
			Zend_Locale::setDefault($locale->toString());
			Zend_Registry::set('Zend_Locale', $locale);

			// Инициализация Zend_Layout, настройка пути к макетам, а также имени главного макета
			Zend_Layout::startMvc(array(
				'layoutPath' => $conf->path->layouts,
				#'layout' => 'index',
			));

			// Получение объекта Zend_Layout
			$layout = Zend_Layout::getMvcInstance();

			// Инициализация объекта Zend_View
			#$view = new Zend_View();
			$view = $layout->getView();

			// since 4/28/2009
			if ( !isset($conf->deprecated->noobjectkey) ) {
				$view->partial()->setObjectKey('model');
				$view->partialLoop()->setObjectKey('model');
			}

			// Настройка расширения макетов
			#$layout->setViewSuffix('tpl');

			// Задание базового URL
			$view->baseUrl = $conf->url->base;

			// Задание пути для view части
			$view->setBasePath($conf->path->views);

			// Сначала скрипт ищется в проекте, а уже потом - в общей либе
			$view->addScriptPath($conf->path->viewsCommon . "scripts"); // 53.2.3. View Script Paths: http://framework.zend.com/manual/en/zend.view.controllers.html#zend.view.controllers.script-paths
			$view->addScriptPath($conf->path->views . "scripts");//@todo!
			#$res = $view->getScriptPaths();
			#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($res, 1) . "</textarea><br>";die;
			#[0] => /mnt/win_d/srv.ntfs/sites/php/zf_ed/application/views/scripts/
			#[1] => /srv/sites/php/library/Zx/application/views/scripts/
			#[2] => /mnt/win_d/srv.ntfs/sites/php/zf_ed/application/views/scripts/

			$view->addHelperPath('Zx/View/Helper/', 'Zx_View_Helper'); // 53.4.2. Helper Paths: http://framework.zend.com/manual/en/zend.view.helpers.html#zend.view.helpers.paths

			// Установка объекта Zend_View
			$layout->setView($view);

			// Настройка расширения view скриптов с помощью Action помошников
			$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
			#$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer'); // использование помощника действия вне контроллера действия
			$viewRenderer->setView($view);

			Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
			#Zend_Controller_Action_HelperBroker::addPrefix('Zx_Controller_Action_Helper');

			// Создание объекта front контроллера
			$frontController = Zend_Controller_Front::getInstance();

			//--< Подключение файла с правилами маршрутизации
			$router = $frontController->getRouter();
			if ( !isset($conf->deprecated->routes) ) {
				$router->addConfig(new Zend_Config_Ini($conf->path->configs . 'routes.ini', 'default'), 'routes');
			} else {
				require $conf->path->settings . 'routes.php';
			}
			#if ($conf->routes) {$frontController->setRouter($router);}
			//-->

			$frontController
				->setBaseUrl($conf->url->base)
				->throwexceptions(true);
			
			self::_initFrontController($frontController, $conf);

			self::_initZFDebug(); // ZFDebug_Controller_Plugin_Debug

			// Smartycode_Http_Conditional (http://smartycode.com/performance/zend-framework-browser-caching/)
			#$frontController->registerPlugin(new Zx_Controller_Plugin_HttpConditional(), 101); //@todo TEST!!!
			//-->

           	// Запуск приложения, в качестве параметра передаем путь к папке с контроллерами
			l('RUN');
			Zend_Controller_Front::run($frontController->getControllerDirectory());
			l('FINISH');
		}

		// local or global handler
		catch (Exception $e) {
			if (!empty($config['handlers']['error'])) {
				Error::catchException($e);
			} else {
				Zx_ErrorHandler::catchException($e);
			}
		}
    }

	/**
	* Установка соединения с базой данных и помещение его объекта в реестр.
	*/
	 public static function setDbAdapter()
	 {
		 #l('DBCONN START');

		// Получение объекта конфигурации из реестра
		$conf = Zend_Registry::get('conf');

		// Подключение к БД, так как Zend_Db "понимает" Zend_Config, нам достаточно передать специально сформированный объект конфигурации в метод factory
		$db = Zend_Db::factory($conf->db); // Zend_Db_Adapter_*

		// 15.1.3.2. Changing the Fetch Mode
		// http://framework.zend.com/manual/en/zend.db.html#zend.db.adapter.select.fetch-mode
		if (!isset($conf->deprecated->fetch_arrays)) {
			$db->setFetchMode(Zend_Db::FETCH_OBJ); // return data in an array of objects
		}

		// TODO?: config it!
		#$db->query('SET names UTF8'); // NB! use init-connect = "SET CHARACTER SET utf8"

		//--< Profiling with Firebug http://framework.zend.com/manual/en/zend.db.profiler.html#zend.db.profiler.profilers.firebug
		if ( !empty($conf->debug->profile) && ($conf->debug->profile == 'FB') )
		{
			$profiler = new Zend_Db_Profiler_Firebug('SQL queries');
			$profiler->setEnabled(true);
			$db->setProfiler($profiler);// Attach the profiler to your db adapter
		}
		//-->

		// Задание адаптера по умолчанию для наследников класса Zend_Db_Table_Abstract
		Zend_Db_Table_Abstract::setDefaultAdapter($db);

		// Занесение объекта соединения c БД в реестр
		Zend_Registry::set('db', $db);

		#l('DBCONN END');
	 }


	/**
	* ZFDebug_Controller_Plugin_Debug / Scienta_Controller_Plugin_Debug
	* http://code.google.com/p/zfdebug/
	* @return mixed
	*/
    protected function _initZFDebug()
    {
		if ( empty($_GET['zdb']) ) {return false;}
		$conf = Zend_Registry::get('conf');
		if (empty($conf->plugin_debugbar)) {return false;}

		$db = Zend_Registry::get('db'); // Zend_Db_Adapter_Mysqli Object || Zend_Db_Adapter_Pdo_Mysql Object

		// Scienta ZF Debug Bar deprecated since 5/23/2009
		if (!empty($conf->deprecated->debugbar))
		{
			$options = array(
				'database_adapter' => $db
			);
			if (LOCALHOST) {
				$options['jquery_path'] = 'http://js/jquery/jquery.js';
			}
			$debug = new Scienta_Controller_Plugin_Debug($options);

		// ZFDebug_Controller_Plugin_Debug
		} else {
			$options = array(
				'plugins' => array(
					'Variables',
					'Registry',
					'Database' => array('adapter' => array('standard' => $db)),
					#'Database' => array('adapter' => $db),
					'File' => array('basePath' => PATH_ROOT),
					'Html',
					'Memory',
					'Time',
					#'Cache' => array('backend' => $cache->getBackend()), //TODO
					'Exception'
			));

			if (LOCALHOST) {
				$options['jquery_path'] = 'http://js/jquery/jquery.js';
			}
			$debug = new ZFDebug_Controller_Plugin_Debug($options);
		}

		if (is_array($conf->plugin_debugbar)) {
			foreach ($conf->plugin_debugbar as $k => $v) {
				$options[$k] = $v;
			}
		}
		$frontController = Zend_Controller_Front::getInstance();
		$frontController->registerPlugin($debug);
	}
	
	function _initFrontController(&$frontController, $conf)
	{
		#$request = $frontController->getRequest();
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($request, 1) . "</textarea><br>";die;
		
		$frontController->setControllerDirectory($conf->path->controllers);
		
/* 		$a = explode('/', $_SERVER['REQUEST_URI']);
		if (!empty($a[1])) {
			// Ищем контроллер в пользовательской дериктории
			if(!file_exists($conf->path->controllers . ucfirst($a[1]) . 'Controller.php'))
			{
				$frontController->setControllerDirectory($conf->path->controllersCommon);
			}
		}
 */		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($frontController->getControllerDirectory(), 1) . "</textarea><br>";die;
		
		//@TODO
		#$frontController->addControllerDirectory($conf->path->controllersCommon);
		#$res = $frontController->getControllerDirectory();
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($res, 1) . "</textarea><br>";die;
		
		//--< Plugins (since 11/21/2008)
		if (!empty($conf->plugins))
		{
			$plugins = $conf->plugins->toArray();

			foreach ($plugins as $v) {
				$plugin = 'Zx_Controller_Plugin_' . $v;
				$frontController->registerPlugin(new $plugin());
			}
		}
/* 		
	// Установка директории контроллеров, используемой по умолчанию
	$front->setControllerDirectory('/controller_dir1/');

	// Переопределяем директорию контроллеров, используемой по умолчанию
	$controllerName = explode('/', $_SERVER['REQUEST_URI']);

	if(isset($controllerName[2]) && $controllerName[1] == 'admin')
	{
		// Ищем контроллер в пользовательской дериктории
		if(file_exists('/controller_dir2/'.ucfirst($controllerName[2]).'Controller.php'))
		{
			$front->setControllerDirectory('/controller_dir2/');
		}
	}
 */
		
		
	}
}