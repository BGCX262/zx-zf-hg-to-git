<?php
/**
* Front End class
* @author Дерягин Алексей (aleksey@deryagin.ru)
* @version 4/18/2010
*/
class Zx_FrontEnd extends Zx_Site
{
	protected $_pages = array();
	protected $_page = 1;
	protected $pageTitle = '';
	#protected $_pathParts = array();

	/**
	*
	* @param
	* @return
	*/
 	function __construct() {
		parent::__construct();
	}

	/**
	* Parse more Zend_Controller_Request->getPathInfo()
	* @param Zend_Controller_Request_Http $request
	* @static
	* @return boolean
	*/
	public static function getPathParts($request)
	{
		$pathParts = $request->getParams();
		l($pathParts, __METHOD__ . ' $request->getParams()');

		$pathParts['host'] = (!empty($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']) . "/"; // SERVER_NAME?
		$pathParts['module'] = $request->getModuleName();

/* 		$pathParts = array(
			'host' => (!empty($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']) . "/", // SERVER_NAME?
			'module' => $request->getModuleName(),
			'controller' => $request->getParam('controller'), // AKA $request->getControllerName()
			'action' => $request->getParam('action'), // AKA $request->getActionName()
			'id' => $request->getParam('id'), // since 5/7/2009 (WAS @deprecated, moved into Zx_IndexController)
			'page' => $request->getParam('page') // since 5/7/2009 (WAS @deprecated, moved into Zx_IndexController)
		);
 */
		// pre-router data @deprecated, use Zend_Controller_Router_Route for route rules!
/* 		$s = substr($_SERVER['REQUEST_URI'], 1, -1);
		$a = explode('/', $s);
		$pathParts['controller0'] = $a[0];
		if (count($a) > 1) {
			$pathParts['action0'] = $a[1];
		}
*/
		$prefix = self::getHttpPrefix();

		$pathParts['path'] = $pathParts['controller'] . "/" . ( ($pathParts['action']  == 'index') ? '' : $pathParts['action'] . "/");
		$pathParts['hostpath'] = $pathParts['host'] . $pathParts['path'];
		$pathParts['url'] = $prefix . $pathParts['path'];
		$pathParts['urlController'] = $prefix . $pathParts['controller'] . "/"; // controller only

		Zend_Registry::set('path', $pathParts);
		Zend_Registry::set('page', (isset($pathParts['page']) ? $pathParts['page'] : 1)); // set to 1 if non-exists
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($pathParts, 1) . "</textarea><br>";die;
/* Array
(
    [host] => il
    [module] => default
    [controller] => index
    [action] => index
    [id] =>
    [url] => http://il/index/index/
)
*/
		return $pathParts;
	}


	/**
	* Force HTTPS (SSL)
	* @uses FrontEnd::checkHTTPS(); OR $this->fe->checkHTTPS();
	* @static
	*/
	public static function checkHTTPS() {
        if (empty($_SERVER['HTTPS'])) {
			$path = Zend_Registry::get('path');
			if (!empty($path['hostpath'])) {
				header("Location: https://" . $path['hostpath']);
				die;
			}
        }
	}


	/**
	* Force HTTP
	* @uses FrontEnd::checkHTTP(); OR $this->fe->checkHTTP();
	* @static
	*/
	public static function checkHTTP() {
        if (!empty($_SERVER['HTTPS'])) {
			$path = Zend_Registry::get('path');
			if (!empty($path['hostpath'])) {
				header("Location: http://" . $path['hostpath']);
				die;
			}
        }
	}


	/**
	* Get page title
	* @param string $page
	* @return string
	* @todo
	*/
	public function getPageTitle($pathInfo = '')
	{
		$this->pageTitle = $this->conf->site->title;
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->pageTitle, 1) . "</textarea><br>";die;

		if (!empty($pathInfo)) {
			$path = $this->getPathParts($pathInfo);
		} else {
			$path = Zend_Registry::get('path');
		}

		if (array_key_exists($path['controller'], $this->_pages)) {
			$this->pageTitle = $this->_pages[$path['controller']][0] . " - " . $title;
		}

		return $this->pageTitle;
	}


	/**
	* Set page title (with a lot of fun and functions)
	* @param mixed $conf (can be array or just string)
	* @return string
	*/
	public function setPageTitle($conf)
	{
		if (empty($conf)) {
			return false;
		}

		// if string, array will e born from it
		if (!is_array($conf)) {
			$conf = array(
				'title' => $conf,
				'div' => '-',
			);
		}

		if (empty($conf['title'])) {
			return false;
		}

		// reset title
		if (!empty($conf['reset'])) {
			$this->pageTitle = '';

		// check if title has set
		} else {
			if (empty($this->pageTitle)) {
				$this->getPageTitle();
			}
		}

		$div = !empty($conf['div']) ? " " . $conf['div'] . " " : '';

		if (!empty($conf['append'])) {
			$this->pageTitle = $this->pageTitle . $div . $conf['title'];
		} else {
			$this->pageTitle = $conf['title'] . $div . $this->pageTitle;
		}

		return $this->pageTitle;
	}


	/**
	* Заголовок страницы
	* @return string
	*/
	public function getTitle() {
		return $this->conf->site->title;
	}

	/**
	* Get image
	* @deprecated
	* @see Zx_Db_Table::getImage()
	* @param
	* @return
	*/
	function getImageFromFolder($folder, $id) {

		$fn = "/public/images/" . $folder . "/" . $folder . sprintf('%04d', $id) . ".jpg";

		$res = '';

		if (file_exists($_SERVER['DOCUMENT_ROOT'] . $fn)) {
			$res = "<p><img src='" . $fn . "' border='0' alt=''></p>";
		}
		return $res;

	}


	/**
	* Получить префикс протокола для URI
	* @static
	* @param
	* @return
	*/
	public static function getHttpPrefix($host = true) {
		if (!empty($_SERVER['HTTPS'])) {
			$res = 'https://';
		} else {
			$res = 'http://';
		}

		if ($host) {
			$res .= $_SERVER['HTTP_HOST'] . "/";
		}

		return $res;
	}

	/**
	* Forget cp1251!
	* @deprecated
	* @param
	* @return
	* @static
	*/
	function s1251($s)
	{
		return iconv('utf-8', 'cp1251', $s);
	}


	/**
	* Шрифт для GD (например, для генерации CAPTCHA)
	* @return string
	*/
	function getFont()
	{
		// for CAPTCHA (Zend_Form_Element_Captcha)
		#$this->_captchaFont = '/usr/share/fonts/ttf/ttf.ms/arial';
		#$this->_captchaFont = '/usr/share/fonts/ttf/dejavu/DejaVuSans.ttf';
		#putenv('GDFONTPATH=' . $font);
		#echo getenv('GDFONTPATH') . "<br/>";
		$font = '/usr/share/fonts/ttf/dejavu/DejaVuSans.ttf';
		return $font;
	}


	/**
	* Get messages from config
	* @uses echo FrontEnd::getMsg('nf');
	* @param string $id
	* @return string
	* @static
	*/
	static function getMsg($id)
	{
		$msg = Zend_Registry::get('msg');

		if (is_array($id))
		{
			if (!empty($msg[$id[0]][$id[1]])) {
				if (count($id) == 2) {
					return $msg[$id[0]][$id[1]];
				} else {
					return sprintf($msg[$id[0]][$id[1]], $id[2]);
				}
			}
		} else {
			if (!empty($msg[$id])) {
				return $msg[$id];
			}
		}

		return false;

/*
		$conf = Zend_Registry::get('conf');
		if (!empty($conf->msg->$id)) {
			return $conf->msg->$id;
		} else {
			return false;
		}
 */
	}


	/**
	 * Универсальный почтальон
	 * @static
	 * @param array $conf
	 * @return boolean
	 */
	static function mail($options)
	{
		$conf = Zend_Registry::get('conf');

		l($conf, __METHOD__ . " conf: ");

		$body = "Здравствуйте! Это автоматически сгенерированное письмо с сайта " . $conf->site->title . ".\n\n" . $options['body'] . "\n\n--\n";
		if (!empty($options['signature'])) {
			$body .= $options['signature'];
		} else {
			$body .= "С уважением,\nадминистрация " . $conf->site->title;
		}

		$mail = new Zend_Mail('utf-8');
		if (!empty($options['html'])) {
			$mail->setBodyHtml($msg);
		} else {
			$mail->setBodyText($body);
		}
		$mail->setFrom($conf->site->admin->email, $conf->site->admin->title);

		if ( (LOCATION != 'local') && !empty($options['to']) )
		{
			$mail->addTo($options['to'], $options['to']);
		} else {
			$mail->addTo($conf->site->admin->email, $conf->site->admin->title);
		}
		$mail->addCc($conf->support->email, $conf->support->title);

		$mail->setSubject($conf->site->url . ': ' . $options['subject']);

		if (LOCATION == 'stable')
		{
			$config = array('post' => 25);
			$transport = new Zend_Mail_Transport_Smtp('127.0.0.1', $config);
			return $mail->send($transport);
		} else {
			return true;
		}
	}


	/**
	* Send feedback
	* @deprecated
	* @param
	* @return boolean
	* @static
	*/
	function feedbackSend($msg, $email, $person) {

		$conf = Zend_Registry::get('conf');

		$mail = new Zend_Mail('utf-8');
		$mail->setBodyText($msg);
		#$mail->setBodyHtml($msg);
		$mail->setFrom($email, $person);
		$mail->addTo($conf->site->admin->email, $conf->site->admin->title);
		$mail->addCc($conf->support->email, $conf->support->title);
		$mail->setSubject("Feedback from " . $conf->site->url);
		if (LOCATION == 'stable') {
			$res = $mail->send();
			return $res;
		} else {
			return false;
		}
	}


	/**
	* Получить имя месяца
	* @param string $ts timestamp
	* @param string $case падеж (родительный и т.д.)
	* @return string
	* @static
	* @todo move to Locale/Date class!
	*/
	function getMonthName($ts, $case = 1) {

		switch ($case) {
		case 1: // Ноябрь
			$res = strftime('%B', $ts);
			break;
		case 2: // Ноября
			$conf = Zend_Registry::get('conf');
			$month = date('m', $ts);

			// Russians not surrender
			if ( substr($conf->locale, 0, 2) == 'ru' ) {
				switch ($month) {
					case 1: $res = 'января'; break;
					case 2: $res = 'февраля'; break;
					case 3: $res = 'марта'; break;
					case 4: $res = 'апреля'; break;
					case 5: $res = 'мая'; break;
					case 6: $res = 'июня'; break;
					case 7: $res = 'июля'; break;
					case 8: $res = 'августа'; break;
					case 9: $res = 'сентября'; break;
					case 10: $res = 'октября'; break;
					case 11: $res = 'ноября'; break;
					case 12: $res = 'декабря'; break;
					default:
						$res = 'херабля';
				}
			} else {
				$res = strftime('%B', $ts);
				break;
			}
		}

		return $res;
	}

	/**
	* Debug dump!
	* @param mixed $v
	* @return
	* @static
	*/
	static public function dump($v)
	{
		if ( LOCATION == 'devel' && defined('ZX_DEBUG')  ) {
			echo 'Zx_FrontEnd::debug:<br><textarea rows=10 cols=100>' . print_r($v, 1) . '</textarea><br>';
		}
	}

	/**
	* Get both remote IP
	* @return array
	* @static
	*/
	static public function getIP()
	{
		$ip = $_SERVER['REMOTE_ADDR'];

		if (LOCALHOST) {$ip = '80.67.245.228';} // NB!

		#$ip = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_ADDR');
		$ip2 = '';
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$s = $_SERVER['HTTP_X_FORWARDED_FOR'];
			if (strpos($s, ',') !== false) {//check for double IP
				$a = explode(',', $s);
				$ip2 = $a[0];
			} else {
				$ip2 = $s;
			}
		}
		return array('ip' => $ip, 'ip2' => $ip2);
	}

	/**
	 * Check if upload with post
	 * @todo compact!
	 * @return boolean
	 */
	function isUpload()
	{
		$upload = new Zend_File_Transfer_Adapter_Http();
		$files = $upload->getFileInfo();#d($files);

		foreach ($files as $file => $info)
		{
			if ($upload->isValid($file))
			{
				#echo 'CHECKPOINT!';die;
				return true;
			}
		}
		return false;
	}

	/**
	 * Set notofication
	 * @param string $s
	 * @param string $ns
	 * @return void
	 */
	static public function setN($s, $ns = 'default')
	{
		$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		$flashMessenger->setNamespace($ns)->addMessage($s);
	}


	/**
	 * AI-pumped move_uploaded_file
	 * @param string $src
	 * @param string $dst
	 * @return boolean
	 */
	function moveUploaded($src, $dst, $conf = null)
	{
		l(__METHOD__);
		#d($conf);
		if (empty($conf['maxFilesize'])) {$conf['maxFilesize'] = 20000;}
		if (empty($conf['name'])) {$conf['name'] = 'file1';}
		#d($conf['maxFilesize']);

		#d($dst);
		$fo = dirname($dst);
		
		$pos = strpos($fo, PATH_PUB);#d($pos);
		
		if ($pos === false) {
			$fo = PATH_PUB . $fo;
			$fo = str_replace('//', '/', $fo);#d($fo);
		}

		if (!is_dir($fo))
		{
			$res = mkdir($fo);
			if (!$res) {
				return false;
			}
		}
		#d(basename($dst));

		$upload = new Zend_File_Transfer_Adapter_Http();
		$upload->addValidator('Size', false, $conf['maxFilesize']);
		#$upload->addValidator('MimeType', false, 'image/png');
		#$upload->addFilter('Rename', array('target' => $fo . '/' . basename($dst), 'overwrite' => true));
		$upload->setDestination($fo . '/');

		#d($upload->getMimeType()); // WHY application/octet-stream?
/*
		if($upload->isValid($file) && $upload->receive())
		#if ($upload->receive())
		{
			$res = true;
		} else {
			$res = false;
			$messages = $upload->getMessages();
			d($messages);
		}
*/

		$messages = null;

		$files = $upload->getFileInfo();
		l($files, __METHOD__ . ' files');
		foreach ($files as $file => $info)
		{
			if ($file != $conf['name']) {continue;}
			#d($file,0);
			if($upload->isValid($file))
			{
				$upload->addFilter('Rename', array('target' => $fo . '/' . basename($dst), 'overwrite' => true));
				$res = $upload->receive($file);
			} else {
				$res = false;
				$messages = $upload->getMessages();
				l($messages, __METHOD__ . ' messages');
			}
		}
/*
		if (!$upload->receive()) {
			$messages = $upload->getMessages();
			echo implode("\n", $messages);
		}
 */

		#$res = move_uploaded_file($src, $fo . '/' . basename($dst));#d($res);
		$res = array('res' => $res, 'messages' => $messages);
		l($res, __METHOD__ . ' res');
		return $res;
	}
}