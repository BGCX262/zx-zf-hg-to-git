<?php
class Zx_Site
{
	function __construct() {
		$this->conf = Zend_Registry::get('conf');
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->conf, 1) . "</textarea><br>";die;
		#echo $this->conf->site->title . "<br/>";
	}
}

// jEdit :indentSize=4:tabSize=4:noTabs=false:lineSeparator=\n:mode=php: