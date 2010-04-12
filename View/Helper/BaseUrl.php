<?php
class Zx_View_Helper_BaseUrl
{
    function baseUrl()
    {
        $front = Zend_Controller_Front::getInstance();
        return $front->getBaseUrl();
    }
}