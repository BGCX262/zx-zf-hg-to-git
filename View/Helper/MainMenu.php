<?php
/**
* Генерация главного меню
* @author Дерягин Алексей (aleksey@deryagin.ru)
* @version 2/6/2009
*/
class Zx_View_Helper_MainMenu extends Zend_View_Helper_Abstract
{
	function mainMenu()
	{
		$content = '';

/*
		if (!empty($this->view->pages->menu)) {
			$a = $this->view->pages->menu;
		} else {
			$a = $this->view->pages;
		}
*/
		$a = $this->view->pages;
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view->pathParts['controller'], 1) . "</textarea><br>";
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view->pathParts['action'], 1) . "</textarea><br>";die;
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($a, 1) . "</textarea><br>";die;

		foreach ($a as $k => $v)
		{
			if (!is_array($v) && method_exists($v, 'toArray')) {
				$v = $v->toArray();// NB!
			}
			#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($v, 1) . "</textarea><br>";

			if (isset($v[4])) {continue;}

			$class = '';
			$controller = $v[2];
			$action = empty($v[3]) ? '' : $v[3]; // drop action if empty!
			#$actionNow = empty($this->view->pathParts['action']) ? '' : $this->view->pathParts['action'];
			#if ($actionNow == 'index') {$actionNow = '';}

			#echo $controller . "<br/>";
			#echo $action . "<br/>";

			if (!empty($action))
			{
				if ( ($this->view->pathParts['controller'] == $controller) && ($this->view->pathParts['action'] == $action) )
				{
					$class = " class='active'";
				}
			} else {
				if ($this->view->pathParts['controller'] == $controller)
				{
					$class = " class='active'";
				}
			}

			$l = $this->view->host . (empty($action) ? $controller : $controller . "/" . $action) . "/";
			$content .= "<li" . $class . "><a href='" . $l . "'>" . $v[0] . "</a></li>\n";
		}

		return $content;
	}
}
//jEdit:indentSize=4:tabSize=4:noTabs=false:lineSeparator=\n:mode=php: