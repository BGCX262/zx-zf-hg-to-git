<?php
class Zx_View_Helper_PageHeadLink extends Zend_View_Helper_Abstract
{
    public function pageHeadLink()
    {
		if (!empty($this->view->pageHeadLink)) {
			foreach ($this->view->pageHeadLink as $v)
			{
				$this->view->headLink()->appendStylesheet("/styles/" . $v . ".css");
			}
		}
		echo $this->view->headLink();
    }
}