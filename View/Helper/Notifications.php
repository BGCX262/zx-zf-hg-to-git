<?php
/**
 * Flash and non-flash notifications
 */
class Zx_View_Helper_Notifications extends Zend_View_Helper_Abstract
{	
	public function notifications($flash = false)
	{
		$res = '';

		if ($flash) {
			$this->view->notifyerr = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->setNamespace('errors')->getMessages();
		}

		if (!empty($this->view->notifyerr))
		{
			if (is_array($this->view->notifyerr))
			{
				l($this->view->notifyerr, __METHOD__ . " notifyerr", Zend_Log::DEBUG);
				foreach ($this->view->notifyerr as $v)
				{
					$res .= '<div class="notifyerr">' . $v . '</div>';
				}
			} else {
				l($this->view->notifyerr, __METHOD__ . " notifyerr (NOT_ARRAY!)");
			}
		} else {
			if ($flash) {
				$this->view->notifymsg = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->setNamespace('default')->getMessages();
			}

			if (!empty($this->view->notifymsg))
			{
				if (is_array($this->view->notifymsg))
				{
					l($this->view->notifymsg, __METHOD__ . " notifymsg", Zend_Log::DEBUG);
					foreach ($this->view->notifymsg as $v)
					{
						$res .= '<div class="notifymsg">' . $v . '</div>';
					}
				} else {
					l($this->view->notifymsg, __METHOD__ . " notifymsg (NOT_ARRAY!)");
				}
			}
		}
		return $res;
   	}
}