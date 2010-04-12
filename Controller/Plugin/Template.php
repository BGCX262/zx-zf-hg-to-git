<?php
/**
* Передача некоторых важных переменных в отображение (view)
* @see http://www.zfforums.com/zend-framework-components-13/model-view-controller-mvc-21/find-out-controller-name-before-dispatcher-call-502.html
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 4/12/2009
*/
class Zx_Controller_Plugin_Template extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
		if (is_null($viewRenderer->view)) {$viewRenderer->init();}// ensure view is created by the view renderer
		$this->view = $viewRenderer->view;
	}

	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		// set up common variables for the view
		$this->view->baseUrl = $request->getBaseUrl();
		$this->view->module = $request->getModuleName();
		$this->view->controller = $request->getControllerName();
		$this->view->action = $request->getActionName();

		// beware, ogre!
		if ( !empty($_GET['zd']) && ($_GET['zd'] == '1') ) {
			echo "Zend_Controller_Request_Http:<br><textarea rows=10 cols=100>" . print_r($request, 1) . "</textarea><br>";
		}
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view, 1) . "</textarea><br>";die;
	}
}