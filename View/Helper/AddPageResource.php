<?php
class Zx_View_Helper_AddPageResource extends Zend_View_Helper_Abstract
{
	/**
	 * @param array $a
	 * @param string $type
	 */
	function addPageResource($a, $type)
	{
		switch ($type) {
			case 'HeadScript':
				$this->view->pageHeadScript = empty($this->view->pageHeadScript) ? $a : array_merge($this->view->pageHeadScript, $a);
				break;
			case 'HeadLink': // CSS
				$this->view->pageHeadLink = empty($this->view->pageHeadLink) ? $a : array_merge($this->view->pageHeadLink, $a);
				break;
			case 'InlineScript':
				$this->view->pageInlineScript = empty($this->view->pageInlineScript) ? $a : array_merge($this->view->pageInlineScript, $a);
				break;
		}
	}
}