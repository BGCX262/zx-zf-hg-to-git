<?php
class Zx_View_Helper_FormatDate// extends Zend_View_Helper_Abstract
{
    private $_formats = array(
		'short' => 'H:m (dd MMMM)',
		'standard' => 'dd MMMM yy',
		'long' => 'dd MMMM yy (H:m)',
		'full' => 'dd.MM.YYYY (H:m:s)',
		'today' => 'H:m',
		'dm' => 'dd.MM', // day and month
		'dmy' => 'dd.MM.YYYY',
		'complete' => 'EEEE, d MMMM Y'
    );
	
    public function formatDate($date, $format = 'dmy', $formatStr = 'dd.mm.yy')
    {
    	$locale = new Zend_Locale('ru_RU.UTF-8');
    	if (!($date instanceof Zend_Date)) {
			$date = new Zend_Date($date, 'YYYY-MM-dd HH:mm:ss', $locale);
    	}
		if (($format != null) && (array_key_exists($format, $this->_formats)))
		{
			return $date->toString($this->_formats[$format], null, $locale);
		} elseif ($formatStr != null) {
			return $date->toString($formatStr, null, $locale);
		} else {
			return $date;
		}
	}    	
}