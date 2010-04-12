<?php
/**
* Contact controller (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 4/8/2009
*/
class ContactController extends MainController
{
	function indexAction()
	{
		//@todo не нравится мне это, попробовать через помощник действия
		$contact = new Zx_Controller_Action_Helper_Contact();
		$res = $contact->run($this, $this->_request);
		$this->textRow('contact');
	}
}