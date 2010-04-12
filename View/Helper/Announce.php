<?php
/**
* Анонс для записи
*/
class Zx_View_Helper_Announce extends Zend_View_Helper_Abstract
{
    public function announce($model, $conf = '')
    {
		if (!empty($model->announce)) {
			$content = $model->announce;
		} else {
			if (!empty($model->txt)) {
				$pos = strpos($model->txt, '.');
				if ($pos) {
					$content = substr($model->txt, 0, $pos+1);
				} else {
					$content = $model->txt;
				}
			} else {
				return false;
			}
		}
		
		if (!empty($conf['div'])) {
			return '<div ' . $conf['div'] . '>' . $content . '</div>'; // @see RPN application/views/scripts/content/pl.item.phtml
		} else {
			return '<br/>' . $content;
		}
    }
}