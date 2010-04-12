<?php
/**
* Главный контроллер (одинаковый для всех сайтов)
* @author Дерягин Алексей (aleksey@deryagin.ru)
* @version 6/16/2009
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
	* Auth user
	* @var
	*/
	protected $user = false;

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
	protected $sectionId;

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
	
	protected $reg; // Zend_Registry

	function init()
	{
		$this->reg = Zend_Registry::getInstance();
		$this->conf = $this->reg->conf;

		$this->initView();

		#$this->fe = new Zx_FrontEnd();
		#$this->view->pathParts = $this->fe->getPathParts($this->_request);
		#$this->view->host = $this->fe->getHttpPrefix(); // используется в хелперах меню

		$this->viewScript = 'main.phtml';

		$this->view->host = Zx_FrontEnd::getHttpPrefix(); // используется в хелперах меню
		$this->view->pathParts = Zx_FrontEnd::getPathParts($this->_request);//@TODO? use $this->p instead
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view->pathParts, 1) . "</textarea><br>";die;
		$this->view->content = '';

		//--> get parameters from request
		$this->id = $this->_getParam('id', null);
		if ($this->id) {
			$this->view->id = $this->id;
		}

		#$code = $this->_request->getParam('code');
		#if ($code) {$this->code = $code;}

		$this->view->topicId = $this->topicId = (int) $this->_getParam('topicId');
		$this->view->subtopicId = $this->subtopicId = (int) $this->_getParam('subtopicId');

		$sectionId = $this->_request->getParam('sectionId');
		if ($sectionId) {
			Zend_Registry::set('sectionId', $sectionId);
			$this->view->sectionId = $this->sectionId = $sectionId;
		}

		$subsectionId = $this->_request->getParam('subsectionId');
		if ($subsectionId) {
			Zend_Registry::set('subsectionId', $subsectionId);
			$this->subsectionId = $subsectionId;
		}

		$this->page = $this->_getParam('page', 1);
		Zend_Registry::set('page', $this->page);

		$this->view->p = $this->p = $this->_request->getParams();
		$this->view->baseUrl = $this->_request->getBaseUrl();
/*
[controller] => stores
[action] => index
[module] => default
*/
		// base current controller URI
		$this->cURI = '/' . $this->p['controller'] . '/';

		// common models init
		if (empty($this->conf->app->FrontEnd)) {
			$this->fe = new Zx_FrontEnd();
		}

		$this->model('Articles', 'db');
		$this->model('Content', 'db');
		$this->model('Feedback', 'db');
		#$this->model('Topics', 'db');
		$this->Topics = new Zx_Db_Table_TopicsTree(); // since 5/21/2009
/* 		$this->Articles = new Zx_Db_Table_Articles();
		$this->Content = new Zx_Db_Table_Content();
		$this->Feedback = new Zx_Db_Table_Feedback();
		$this->Topics = new Zx_Db_Table_Topics();
 */

 		// Создание экземпляров классов
 		if (!empty($this->conf->libload)) {
			$a = $this->conf->libload->toArray();
			foreach ($a as $k => $v) {
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
			$this->setViewVar('mainHeader', $s);
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
			$this->setViewVar('pageTitle', $s);
		} else {
			$this->setVar('pageTitle', $s);	
		}
	}

	/**
	* Авторизация пользователей
	* @todo plugin OR model
	* @return boolean
	*/
	protected function initAuth()
	{
		$this->authAllowed = true;
		
		$auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
			$this->user = $auth->getIdentity();
			return true;
		}
		return false;
	}


	/**
	* Инициализация строковых значений, зависит от кодировки
	* @uses ED, CD etc
	* @return void
	*/
	function initStrings()
	{
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

		$this->setVar('msg', $this->conf->msg->toArray()); // all messages! fx see in contact view script: echo '<br/><b>', $this->msg['feedback']['sent'], '</b>';
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view->msg, 1) . "</textarea><br>";die;

		$this->setVar('title', $this->fe->getTitle());

		// <title>
		if (!empty($this->cTitle)) {
			$this->setVar('pageTitle', $this->fe->setPageTitle($this->cTitle));
		} else {
			$this->setVar('pageTitle', $this->fe->getPageTitle());
		}
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
	function setVar($name, $s, $load = false, $rewrite = false) {
		// load from .phtml
		if ($load) {
			$s = $this->view->render($s . ".phtml");
		}

		// iconv
		$s = $this->_($s);

		if (is_array($this->view->$name)) {
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
	* Instant and refactored variant of setVar :)
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
	* iconv
	* @param
	* @return
	*/
	function _($s) {
		if ( !empty($s) && ($this->_charset != 'UTF-8') && !is_array($s) ) {
			return FrontEnd::getCP1251($s);
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
				$modelClass = "Zx_Db_Table_" . $model;
				break;
			default:
				$modelClass = $model;
				break;
			}
		} else {
			$modelClass = $model;
		}

		if (empty($this->$model)) {
			$this->$model = new $modelClass();
			if ($this->NLS) {
				$this->$model->setNLS($this->NLS, $this->NLSId);
			}
		}
		return $this->$model;
	}
	
	/**
	* Slashed Zend_View::url()
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
* DEPRECATED!
*/
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
}
