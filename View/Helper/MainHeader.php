<?php
/**
* Генерация главного заголовка
* Если заголовок ещё не задан, генерация из конфига
* @version 12/11/2008
*/
class Zx_View_Helper_MainHeader extends Zend_View_Helper_Abstract
{
	function mainHeader()
	{
		if (!empty($this->view->mainHeader)) {
			return $this->view->mainHeader;
		}
		
		// генерация из конфига
		#$conf = Zend_Registry::get('conf');
		#if (empty($conf->pages)) {return '';}
		
/* 		if (!empty($this->view->pages->menu)) {
			$a = $this->view->pages->menu;
		} else {
			$a = $this->view->pages;
		}
*/

		if (empty($this->view->pages)) {return '';}

		$a = $this->view->pages;
		if (!is_array($a) && method_exists($a, 'toArray')) {
			$aa = $a->toArray();
		} else {
			$aa = $a;
		}

		foreach ($aa as $k => $v) {
			$controller = $v[2];
			$action = empty($v[3]) ? 'index' : $v[3];
			
			if ( ($this->view->pathParts['controller'] == $controller) && ($this->view->pathParts['action'] == $action) )
			{
				$this->view->mainHeader = $v[0];
				return $this->view->mainHeader;
			}
		}

		return '';
	}
}