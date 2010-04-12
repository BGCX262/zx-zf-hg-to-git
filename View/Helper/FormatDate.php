<?php
class Zx_View_Helper_FormatDate// extends Zend_View_Helper_Abstract
{
    private $date_formats = array(
    	"short" => "H:m (dd MMMM)",
    	"standard" => "dd MMMM yy",
    	"long" => "dd MMMM yy (H:m)",
    	"today" => "H:m"
    );
	
    public function formatDate($date, $format = "short", $formatStr = "dd MMMM yy")
    {
    	$locale = new Zend_Locale('ru_RU.CP1251');
    	if (!($date instanceof Zend_Date)) {
			$date = new Zend_Date($date, 'YYYY-MM-dd HH:mm:ss', $locale);
    	}
		if ($format == "short") {
			//return iconv('utf8', 'cp1251', $date->toString($this->date_formats[$date->isToday() ? "today" : "short"], null, $locale));
            if($date->isToday())
                return '<font color="red">'.$date->toString($this->date_formats['today']).' (сегодня)</font>';
            else
            return iconv('UTF-8', 'WINDOWS-1251', $date->toString($this->date_formats[ "short"], null, $locale));
		}
		elseif (($format != null) && (array_key_exists($format, $this->date_formats))) {
			return iconv('UTF-8', 'WINDOWS-1251', $date->toString($this->date_formats[$format], null, $locale));
            
		} elseif ($formatStr != null){
			return iconv('UTF-8', 'WINDOWS-1251', $date->toString($formatStr, null, $locale));
		} else {
			return $date;
		}
	}    	
}