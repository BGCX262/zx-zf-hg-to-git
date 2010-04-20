<?php
/**
 * Url Trailed (with trailing slash)
 */
class Zx_View_Helper_Urlt# extends Zend_View_Helper_Abstract
{
    public function urlt(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
		$router = Zend_Controller_Front::getInstance()->getRouter();
		return $router->assemble($urlOptions, $name, $reset, $encode) . '/';
    }
}