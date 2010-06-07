<?php
/**
* Главный AJAX контроллер (одинаковый для всех сайтов)
* @author Дерягин Алексей (aleksey@deryagin.ru)
*/
class Zx_Controller_Ajax extends Zx_Controller_Action
{
	protected $_errorMessage = '';
	protected $_isJSON = true; // возврат результата AJAX запросов в виде JSON (since 15.02.2010)
	protected $_resJSON = true;
/*
	public function preDispatch()
    {
		parent::preDispatch();
		#l($this->getRequest()->isXmlHttpRequest(), 'isXmlHttpRequest', Zend_Log::DEBUG);

        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('commentform', 'html')
			->initContext();
    }
*/
	function init()
    {
		#$this->_helper->viewRenderer->setNoRender();
		#$this->_helper->layout->disableLayout(); // ajaxContext сам должен отключать макет. Возможно вы забыли передать параметр format=html и создать шаблон .html.phtml
		if (!$this->_isAJAX()) {return $this->_throwError();}
		parent::init();

    }

	/**
	* AJAX POST detection
	*/
	protected function _isAJAX()
	{
		if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest())
		{
			return true;
		} else {
			l(null, __METHOD__ . ': NOT_AJAX_REQUEST', Zend_Log::WARN);
			$this->_errorMessage = 'Only AJAX requests allowed!';
			return false;
		}
	}

	/**
	* AJAX POST result as JSON
	*/
	protected function _resultJSON($a, $aa = null)
	{
		if (is_null($aa)) {
			if (is_array($a)) {
				$res = $a[0];
				$aaa = array( 'result' => $res, 'content' => $a[1] );
			} else {
				$res = $a;
				$aaa = array( 'result' =>  $res);
			}
		} else {
			$res = $a;
			if (is_array($aa)) {
				$aaa = array_merge( array( 'result' => $res ), $aa );
			} else {
				$aaa = array( 'result' => $res, 'content' => $aa );
			}
		}
		#l($aaa, __METHOD__ . ': aaa', Zend_Log::DEBUG);

		$json = json_encode($aaa);
		#l($json, __METHOD__ . ': $json', Zend_Log::DEBUG);
		$this->getResponse()->appendBody($json);
		$this->_helper->viewRenderer->setNoRender();

		if ($res == 'ok') {
			$this->_resJSON = true;
		} else {
			$this->_resJSON = false;
		}
	}

	protected function _throwError($message = '')
	{
		if (empty($message)) {
			$message = $this->_errorMessage;
		}
		throw new Zend_Controller_Action_Exception($message, 404);
	}

	protected function _renderError($message)
	{
		if ($this->_isJSON) {
			$this->_resultJSON($message);
		} else { // old way
			$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); // deprecated!
			$this->getResponse()->appendBody($message);
			$this->_helper->viewRenderer->setNoRender();
		}
	}

	/**
	 * @deprecated
	 * @see _resultJSON
	 */
	protected function _resultAJAX($res) {
		if ($res == 'ok') {
			return $this->_resultJSON($res);
		} else {
			return $this->_renderError($res);
		}
	}

}
