<?php
/**
* Контроллер раздела "Карта сайта"
* @author Дерягин Алексей (aleksey@deryagin.ru)
*/
class SitemapController extends MainController
{
	function indexAction()
	{
		$this->textRow('info');
		$this->renderScript($this->viewScript);
	}
}