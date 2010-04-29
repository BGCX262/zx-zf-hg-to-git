<?php
/**
* @deprecated
*/
class Zx_View_Helper_HeadUserStyle extends Zend_View_Helper_Abstract
{
    public function headUserStyle()
    {
		if (!empty($this->view->headUserStyles)) {
			foreach ($this->view->headUserStyles as $k => $v) {
				$this->view->headLink()->appendStylesheet("/styles/" . $v . ".css");
			}
		}
    }
}