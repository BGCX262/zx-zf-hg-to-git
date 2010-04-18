<?php
/**
* Контроллер раздела "Информация"
* @author Дерягин Алексей (aleksey@deryagin.ru)
*/
class InfoController extends MainController
{
	function indexAction()
	{
		$this->textRow('info');
		$this->renderScript($this->viewScript);
	}


	/**
	* "About Us" article
	* @return
	*/
	function aboutAction()
	{
		$this->textRow('about');// get article
		$this->renderScript($this->viewScript);
	}
	

	/**
	* Specific article
	* @return
	*/
	function itemAction()
	{
		$this->textRow($this->id);// get article
		$this->renderScript($this->viewScript);
	}
}