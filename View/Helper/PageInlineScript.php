<?php
/**
 * inlineScript() wrapper
 */
class Zx_View_Helper_PageInlineScript extends Zend_View_Helper_Abstract
{
    public function pageInlineScript()
    {

		if ( empty($this->view->pageInlineScript) || (!is_array($this->view->pageInlineScript)) ) {return;}

		foreach ($this->view->pageInlineScript as $fn)
		{
			if ($fn[0]=='?') { // no cache!
				$this->view->inlineScript()->appendFile('/scripts/' . substr($fn, 1) . '.js?v=' . date('dmYH'));
			} elseif ($fn[0]=='!') {
				$this->view->inlineScript()->appendFile('/scripts/' . substr($fn, 1)); // as is!
			} else {
				$this->view->inlineScript()->appendFile('/scripts/' . $fn . '.js');
			}
		}

		echo $this->view->inlineScript();
    }
}