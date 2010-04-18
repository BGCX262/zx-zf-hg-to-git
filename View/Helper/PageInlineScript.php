<?php
/**
 * inlineScript() wrapper
 */
class Zx_View_Helper_PageInlineScript extends Zend_View_Helper_Abstract
{
    public function pageInlineScript()
    {
		// particle scripts
		if (!empty($this->view->pageInlineScript)) {
			foreach ($this->view->pageInlineScript as $fn)
			{
				if ($fn[0]=='?') {
					$this->view->inlineScript()->appendFile(h('js') . substr($fn, 1) . '.js?v=' . date('dmYH'));
				} else {
					$this->view->inlineScript()->appendFile(h('js') . $fn . '.js');
				}
			}
		}
		echo $this->view->inlineScript();
    }
}