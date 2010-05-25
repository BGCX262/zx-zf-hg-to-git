<?php
/**
 * Url Trailed (with trailing slash)
 */
class Zx_View_Helper_Urlt# extends Zend_View_Helper_Abstract
{
    public function urlt($urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
		if (!is_array($urlOptions))
		{
			$name = $urlOptions;
			$urlOptions = array();
		}

		$router = Zend_Controller_Front::getInstance()->getRouter();
		return $router->assemble($urlOptions, $name, $reset, $encode) . '/';
    }
}