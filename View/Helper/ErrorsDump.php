<?php
/**
* Анонс для записи
*/
class Zx_View_Helper_ErrorsDump extends Zend_View_Helper_Abstract
{
    public function errorsDump()
    {
		if (empty($this->view->errors)) {return false;}
		
		if (!is_array($this->view->errors)) {
			$errors = array($this->view->errors);
		} else {
			$errors = $this->view->errors;
		}
		
		// errors
		foreach ($errors as $v) {
			echo '<div class="error">' . $this->view->msg['error'] . ': ' . $v . '</div>';
		}
    }
}

