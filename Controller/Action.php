<?php
/**
* Главный контроллер (одинаковый для всех сайтов)
* @author Дерягин Алексей (aleksey@deryagin.ru)
*/
class Zx_Controller_Action extends Zend_Controller_Action
{
	/**
	* @var array
	*/
	protected $_errors = array();

	/**
	* UTF-8 encoding by default
	* @var string
	*/
	protected $_charset = 'UTF-8';

	/**
	* Auth user (moved to registry!)
	* @var
	*/
	#protected $user = false;

	/**
	* Parameters
	* @var
	*/
	protected $p;

	/**
	* Parameter: ID
	* @var
	*/
	protected $id = null;

	/**
	* Parameter: section
	* @var
	*/
	protected $sectionId = null;

	/**
	* Parameter: subsection
	* @var
	*/
	protected $subsectionId;

	/**
	* Защита переменных представления от перезаписи отключена
	* @var
	*/
	protected $rewriteViewVars = false;

	/**
	* Флаг NLS
	* @var
	*/
	protected $NLS = false;

	/**
	* NLS ID
	* @var
	*/
	protected $NLSId = 1;
	
	protected $paginatorFile = 'partials/paginator.phtml';
	
	protected $authAllowed = false; // no FE-users 
	protected $authRegistrationAllowed = false; // no FE-users registration (use BE!)
	protected $authHTTPS = true; // via protected protocol
	
	#protected $reg; // Zend_Registry

	protected $_flashMessenger = null;

	function init()
	{
		$this->conf = Zend_Registry::get('conf');

		$this->initView();

		$this->viewScript = 'main.phtml';

		$this->view->host = Zx_FrontEnd::getHttpPrefix(); // используется в хелперах меню
		$this->view->pathParts = Zx_FrontEnd::getPathParts($this->getRequest());//@TODO? use $this->p instead
		$this->view->divider = ' - '; // title / headers divider
		$this->view->content = '';

		//--> get parameters from request
		$this->id = $this->_getParam('id', null);
		if ($this->id) {
			$this->view->id = $this->id;
		}

		$this->view->topicId = $this->topicId = (int) $this->_getParam('topicId');
		$this->view->subtopicId = $this->subtopicId = (int) $this->_getParam('subtopicId');

		$sectionId = $this->getRequest()->getParam('sectionId');
		if ($sectionId) {
			Zend_Registry::set('sectionId', $sectionId);
			$this->view->sectionId = $this->sectionId = $sectionId;
		}

		$subsectionId = $this->getRequest()->getParam('subsectionId');
		if ($subsectionId) {
			Zend_Registry::set('subsectionId', $subsectionId);
			$this->subsectionId = $subsectionId;
		}

		$this->page = $this->_getParam('page', 1);
		Zend_Registry::set('page', $this->page);

		$this->view->p = $this->p = $this->getRequest()->getParams();
/*
[controller] => stores
[action] => index
[module] => default
*/
		$this->view->baseUrl = $this->getRequest()->getBaseUrl();
		$this->view->requestUri = $this->getRequest()->getRequestUri();

		// base current controller URI
		$this->cURI = '/' . $this->p['controller'] . '/';

		// common models init
		if (empty($this->conf->app->FrontEnd)) {
			$this->fe = new Zx_FrontEnd();
		}

		$this->model('Articles', 'db');#$this->Articles = new Zx_Db_Table_Articles();
		$this->model('Content', 'db');#$this->Content = new Zx_Db_Table_Content();
		#$this->model('Feedback', 'db');#$this->Feedback = new Zx_Db_Table_Feedback();
		#$this->model('Topics', 'db');#$this->Topics = new Zx_Db_Table_Topics();
		$this->Topics = new Zx_Db_Table_TopicsTree(); // since 5/21/2009

 		// Создание экземпляров классов
 		if (!empty($this->conf->libload))
		{
			$a = $this->conf->libload->toArray();
			foreach ($a as $k => $v)
			{
				if ($v === true) {$v = $k;}
				$this->$k = new $v();
			}
		}

/*
Array
(
    [id] => women
    [module] => default
    [controller] => index
    [action] => index
)
*/
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view->p, 1) . "</textarea><br>";die;
		//<--

		$this->initStrings();

		//TODO: move to projects?
		if (is_object($this->conf->auth) && empty($this->conf->auth->strict) )
		{
			$this->initAuth($this->conf->auth);
		}

        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->messages = $this->_flashMessenger->getMessages();#d($this->view->messages);

	}

	function postDispatch()
	{
		parent::postDispatch();
   		$js = $this->view->render('partials/notifications.phtml');#d($js);
		if ($js)
		{
			$this->addPageResource(array('jquery.notifications', 'inline' => $js), 'HeadScript');
			$this->addPageResource(array('jquery.notifications'), 'HeadLink');
			#$this->view->pageHeadScript[] = array('jquery.notifications', 'inline' => $js); // for HeadScript view helper
			#$this->view->pageHeadLink[] = array('jquery.notifications'); // for HeadLink view helper
		}

    }


   /**
* GENERAL METHODS
*/

	protected function setNLS ($v = true)
	{
		$this->NLS = $v;
	}

	/**
	* Set page header (body)
	* @param
	* @return
	*/
	protected function setHeader($s = '', $fast = true)
	{
		if ($fast) { // no complex calculations, just set it!
			$this->view->mainHeader = $s;
		} else {
			$this->setVar('mainHeader', $s);	
		}
	}


	/**
	* Set page <title></title>
	* @param string $s
	* @param boolean $fast
	* @return
	*/
	protected function setTitle($s = '', $fast = true)
	{
		if ($fast) { // no complex calculations, just set it!
			$this->view->pageTitle = $s;
		} else {

			if (!empty($this->view->pageTitle)) {
                $this->view->pageTitle .= $this->view->divider;
            }

          	$this->setVar('pageTitle', $s);
		}
	}

	/**
	* Авторизация пользователей
	* @todo plugin OR model
	* @todo не дублировать значения из конфига без надобности!
	* @return boolean
	*/
	protected function initAuth($auth = null)
	{
		$this->authAllowed = true;

		if (isset($auth->authHTTPS)) {
	       	$this->authHTTPS = (bool) $auth->authHTTPS;
        } else {
			$this->authHTTPS = true;
		}

		if (!empty($auth->userRegistrationAllowed)) {
	       	$this->userRegistrationAllowed = true;
		}

		if (isset($auth->loginRedirect)) {
	       	$this->loginRedirect = $auth->loginRedirect;
        } else {
			$this->loginRedirect = '/';
		}

		if (isset($auth->loginActionDisplayForm)) {
	       	$this->loginActionDisplayForm = (bool) $auth->loginActionDisplayForm;
        } else {
			$this->loginActionDisplayForm = true;
		}
		
		$auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity())
		{
			$this->view->identity = $auth->getIdentity();
			#Zend_Registry::set('identity', $identity); // DRY! use $auth!
			#d($this->view->identity);
			return true;
		} else {
			$this->view->identity = null;
			return false;
		}
	}


	/**
	* Инициализация строковых значений, зависит от кодировки
	* @uses ED, CD etc
	* @return void
	*/
	function initStrings()
	{
		l(__METHOD__. ' checkpoint');
		$this->view->pages = $this->conf->pages;// Главное навигационное меню
		
		if (!empty($this->conf->pages)) {
			$this->view->aPages = $this->aPages = $this->conf->pages->toArray(); // pages array
		}

		// base current controller title
		if (!empty($this->aPages[$this->p['controller']])) {
			$this->cTitle = $this->aPages[$this->p['controller']][0];
			$this->setHeader($this->cTitle);
		} else {
			$this->setHeader();
		}

		$this->setVar('msg', $this->conf->msg->toArray()); // @deprecated!!! all messages! fx see in contact view script: echo '<br/><b>', $this->msg['feedback']['sent'], '</b>';
		Zend_Registry::set('msg', $this->conf->msg->toArray());

		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view->msg, 1) . "</textarea><br>";die;

		$this->setVar('title', $this->fe->getTitle());

		// <title>
		if (!empty($this->cTitle)) {
			#$this->setVar('pageTitle', $this->fe->setPageTitle($this->cTitle));
			$this->setTitle($this->fe->setPageTitle($this->cTitle));
		} else {
			$this->setTitle($this->fe->getPageTitle());
			#$this->setVar('pageTitle', $this->fe->getPageTitle());
		}
		#d($this->view->pageTitle);

		$this->view->notifyerr = array();
		$this->view->notifymsg = array();
    }

	/**
	* Выбрать запись контента (отдельный текст)
	* @param string $where
	* @param array $conf
	* @return boolean
	*/
	function textRow($where, $conf = array())
	{
		$row = $this->getRow('Articles', $where, $conf);
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($row, 1) . "</textarea><br>";die;
		if (empty($row)) {return false;}

		if (empty($conf['headerOnly'])) {

			// announce
			if (!empty($row->announce)) {
				$this->setVar('contentAnnounce', $row->announce);
			}

			// text
			$this->setContent($this->getContent($row->txt));
		}

		$this->setVar('mainHeader', $row->title, 0, 1);
		return true;
	}

	/**
	* 1 запись контента
	* @param string $where
	* @param array $conf
	* @return mixed
	*/
	protected function contentRow($where, $conf = array())
	{
		$row = $this->getRow('Content', $where, $conf);
		if (empty($row)) {return false;}

		if (!empty($conf['render'])) {
			$this->setContent($this->getContent($row->txt));
			$this->render();
			return true;
		} else {
			return $row;
		}
	}


	/**
	* Получить запись раздела
	* @param string $where условие / id / code
	* @param boolean $render
	* @return mixed
	*/
	protected function topicRow($where, $render = true) {

		$row = $this->getRow('Topics', $where);
		if (empty($row)) {return false;}

		$this->setContent($this->getContent($row->txt));
		$this->setVar('mainHeader', $row->title, 0, 1);
		$this->setVar('pageTitle', $row->title . " - " . $this->view->pageTitle);

		if ($render) {
			$this->render();
			return true;
		} else {
			return $row;
		}
	}

	/**
	* Get content by right way
	* @static
	* @return string
	*/
	protected function getContent($s) {

		//--> выбросить анонс, если он хранится в теле сообщения
		// @deprecated	@todo: сделать в БД отдельное поле для анонса!
		$pos = strpos($s, '|');
		if ($pos !== false) {
			$a = explode('|', $s, 2);
			$s = $a[1];
		}
		//--<

		if (strpos($s, '<p>') !== false) {
			return $s;
		}

		$ss = str_replace("\n", "</p>\n<p>", $s);

		return "<p>" . $ss . "</p>";

	}

	/**
	* Установка переменной страницы (view)
	* @param string $name
	* @param string $s
	* @param boolean $load
	* @return boolean
	*/
	function setVar($name, $s, $load = false, $rewrite = false)
	{
		// load from .phtml
		if ($load) {
			$s = $this->view->render($s . ".phtml");
		}

		#$s = $this->_($s); // iconv

		if (is_array($this->view->$name))
		{
			array_push($this->view->$name, $s);
		} else {
			if ( !$this->rewriteViewVars && !$rewrite && !empty($this->view->$name) ) {
				$this->view->$name .= $s;
			} else {
				$this->view->$name = $s;
			}
		}
		return true;
	}


	/**
	* iconv
	* @deprecated we use UTF-8 only!
	* @param
	* @return
	*/
	function _($s)
	{
		if ( ($this->_charset != 'UTF-8') && !empty($s) && !is_array($s) )
		{
			return FrontEnd::s1251($s);
		} else {
			return $s;
		}
	}


	/**
	* Установка контента страницы
	* @access public (must be!!!)
	* @param string $s
	* @param boolean $load
	* @return boolean
	*/
	public function setContent($s, $load = false) {
		return $this->setVar('content', $s, $load);
	}

	/**
	* Получить объект модели
	* @example model('Store_Sections')
	* @param string $model
	* @return boolean
	*/
	function model($model, $code = '')
	{
		if (!empty($code)) {
			switch ($code) {
			case 'db':
			case 'zx':
				$modelClass = "Zx_Db_Table_" . $model;
				break;
			default:
				$modelClass = $model;
				break;
			}
		} else {
			$modelClass = $model;
		}

		if (empty($this->$model))
		{
			$this->$model = new $modelClass();
			if ($this->NLS) {
				$this->$model->setNLS($this->NLS, $this->NLSId);
			}
		}
		return $this->$model;
	}
	

/**
* DEPRECATED!
*/
	/**
	* Slashed Zend_View::url()
	* @deprecated
	* @see Zx_View_Helper_Urlt
	* @param array $urlOptions
	* @param string $name
	* @return string
	* @since 6/1/2009
	*/
	function url($urlOptions, $name, $reset = false)
	{
		return $this->view->url($urlOptions, $name, $reset) . '/';
	}

	/**
	* model() alias
	* @deprecated
	*/
	function getModel($model, $code = '') {
		return $this->model($model, $code);
	}

	/**
	* Подготовка конфигурации для SQL выборки
	* @deprecated since 11/26/2008
	* @see Zx_Db_Table
	* @param string $model model name
	* @param string $action action name
	* @return array
	*/
	protected function confSQL($model, $action) {
		return $this->$model->confSQL($action);
	}


	/**
	* Записи контента
	* @param string $where
	* @param array $conf
	* @return mixed
	* @deprecated since 10/14/2008, use $this->getRows() instead
	*/
/*
	protected function contentRows($where, $conf = array()) {
		return $this->getRows('Content', $where, $conf);
	}
*/

	/**
	* Получить 1 запись контента
	* Универсальный метод
	* @deprecated since 11/26/2008
	* @see Zx_Db_Table
	* @param string $model
	* @param string $where условие / id / code
	* @param array $conf
	* @return Zend_Db_Table_Row or FALSE
	*/
	protected function getRow($model, $where = '', $conf = array()) {
		return $this->$model->getRow($where, $conf);
	}


	/**
	* Записи контента
	* @deprecated since 11/26/2008
	* @see Zx_Db_Table
	* @param string $model
	* @param string $where условие / id / code
	* @param array $conf
	* @return Zend_Db_Table_Rowset or FALSE
	*/
	protected function getRows($model, $where = '', $conf = array()) {
		return $this->$model->getRows($where, $conf);
	}

	/**
	* Компоновка SQL запроса (Zend_Db_Table_Select wrapper)
	* @deprecated since 11/26/2008
	* @see Zx_Db_Table
	* @param string $model
	* @param string $where
	* @param array $conf
	* @return
	*/
	protected function setSQL($model, $where = '', $conf = array()) {
		return $this->$model->setSQL($where, $conf);
	}

	/**
	* Instant and refactored variant of setVar :)
	* @deprecated
	* @param
	* @return void
	*/
	function setViewVar($name, $value)#, $conf = null
	{
		$this->view->$name = $value;
/* 		switch ($name){
			case '':
				break;
			default:
				$this->view->$name = $value;
		}
*/
	}

	/**
	 * Set notification via Flash Messanger
	 * @deprecated
	 * @uses Table::setN
	 * @param string $s
	 */
	function setN($s, $ns = 'default')
	{
		$this->_flashMessenger->setNamespace('errors')->addMessage(FrontEnd::getMsg(array('update', 'fail')));
		if (is_array($s)) {
			foreach ($s as $k => $v)
			{
				$this->_flashMessenger->setNamespace($ns)->addMessage(current($v));
			}
		} else {
	        $this->_flashMessenger->setNamespace($ns)->addMessage($s);
		}
    }


	/**
	 *
	 * @param array $a
	 */
	function addPageResource($a, $type)
	{
		switch ($type) {
			case 'HeadScript':
				$this->view->pageHeadScript = empty($this->view->pageHeadScript) ? $a : array_merge($this->view->pageHeadScript, $a);
				#d($this->view->pageHeadScript, 0);
				break;
			case 'HeadLink':
				$this->view->pageHeadLink = empty($this->view->pageHeadLink) ? $a : array_merge($this->view->pageHeadLink, $a);
				break;
			case 'InlineScript':
				$this->view->pageInlineScript = empty($this->view->pageInlineScript) ? $a : array_merge($this->view->pageInlineScript, $a);
				break;
		}

	}

}
