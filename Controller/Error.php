<?php
/**
* Error Controller (for view script application/views/scripts/error/error.phtml)
* @package QuickStart
* @example http://framework.zend.com/docs/quickstart/create-an-error-controller-and-view
* @see Zx_ErrorHandler
*/
class Zx_Controller_Error extends Zend_Controller_Action
{
	/**
	* Error action
	* @return void
	*/
    public function errorAction()
    {
        // Ensure the default view suffix is used so we always return good
        // content
        $this->_helper->viewRenderer->setViewSuffix('phtml');

        // Grab the error object from the request
        $errors = $this->_getParam('error_handler');

        // $errors will be an object set as a parameter of the request object,
        // type is a property
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        // pass the environment to the view script so we can conditionally
        // display more/less information
        $this->view->env       = $this->getInvokeArg('env');

        // pass the actual exception object to the view
        $this->view->exception = $errors->exception;

        // pass the request to the view
        $this->view->request   = $errors->request;
    }

/*
	public function errorAction()
    {
		$this->_redirect('/'); // /index/index
		// You will also need to create a view script in application/views/scripts/error/error.phtml; sample content might look like:
		// http://framework.zend.com/manual/en/zend.controller.html#zend.controller.quickstart.go.errorhandler
    }
*/
}
