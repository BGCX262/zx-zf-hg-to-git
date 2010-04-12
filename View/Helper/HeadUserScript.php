<?php
class Zx_View_Helper_HeadUserScript extends Zend_View_Helper_Abstract
{
	protected $jqueryVer = '1.3'; // http://code.google.com/intl/ru/apis/ajaxlibs/documentation/index.html#jquery
	protected $jqueryuiVer = '1.7'; // http://code.google.com/intl/ru/apis/ajaxlibs/documentation/index.html#jqueryUI

    public function headUserScript()
    {
		if (!empty($this->view->headUserScripts))
		{
			foreach ($this->view->headUserScripts as $k => $v)
			{
				if ($k === 'inline')
				{
					$this->view->headScript()->appendScript($v);
				} else {
					switch ($v)
					{
						case 'jquery': // jQuery
							if (LOCALHOST) {
								$this->view->headScript()->appendFile('http://js/jquery/jquery.js');
							} else {
								$this->view->headScript()->appendFile('/scripts/jquery.js');
							}
							break;
						case 'jqueryui': // jQuery + jQuery UI
							if (LOCALHOST) {
								$this->view->headScript()->appendFile('http://js/jquery/jquery.js');
								$this->view->headScript()->appendFile('http://js/jquery/jquery-ui.js');
							} else {
								$this->view->headScript()->appendFile('http://www.google.com/jsapi');
								$this->view->headScript()->appendScript("google.load('jquery', '{$this->jqueryVer}'); google.load('jqueryui', '{$this->jqueryuiVer}');");
							}
							break;
						case 'jsapi_jquery': // Google API + jQuery
							if (LOCALHOST) {
								$this->view->headScript()->appendFile('http://js/jquery/jquery.js');
							} else {
								$this->view->headScript()->appendFile('http://www.google.com/jsapi');
								$this->view->headScript()->appendScript("google.load('jquery', '{$this->jqueryVer}');");
							}
							break;
						case 'jsapi': // Google API
							$this->view->headScript()->appendFile('http://www.google.com/jsapi');
							break;
						#case 'jqueryui': // @deprecated, use jsapi_jqueryui
						#	$this->view->headScript()->appendScript("google.load('jquery', '{$this->jqueryVer}'); google.load('jqueryui', '{$this->jqueryuiVer}');");
						#	break;
						default:
							#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($v, 1) . "</textarea><br>";
							$this->view->headScript()->appendFile($v);
							break;
					}
				}
			}
		}
    }
}