<?php
class UserController extends MainController
{
	function indexAction() {
		$this->setVar('headerPage', 'Моя страница');
		$this->setContent($this->todo);
		$this->renderScript($this->viewScript);
	}

	function profileAction() {
		$this->setVar('headerPage', 'Моя страница - настройки');
		$this->setContent($this->todo);
		$this->renderScript($this->viewScript);
	}

}