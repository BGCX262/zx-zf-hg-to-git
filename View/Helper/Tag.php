<?php
/**
* HTML universal wrapper
* @param string $tag
* @param string/array $conf
* @return string
*/
class Zx_View_Helper_Tag
{
    function tag($tag, $conf = '')
    {
		// simple tag
		if (empty($conf)) {
			if ($tag[0] == '/') {
				return "<" . $tag . ">\n";
			} else {
				return "<" . $tag . ">";
			}
			
		} elseif (!is_array($conf)) { // $conf as content
			return "<" . $tag . ">" . $conf . "</" . $tag . ">\n";
		}
		
		switch (strtolower($tag)) {
			case 'fieldset':
				$s = "<fieldset>\n";
				if (!empty($conf['legend'])) {
					$s.= "<legend>" . $conf['legend'] . "</legend>\n";
				}
				if (!empty($conf['content'])) {
					$s.= $conf['content'] . "</fieldset>\n";
				}
				break;
			default:
				$s = "<" . $tag . " ";
				foreach ($conf as $k => $v) {
					$s.= $k . "='" . $v . "'";
				}
				$s.= '>';
		}
		
		return $s;
    }
}