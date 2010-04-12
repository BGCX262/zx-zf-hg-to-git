<?php
// http://robertbasic.com/blog/myurl-view-helper-for-zend-framework/
// Usage:
// <?= $this->myUrl($this->url(array('param2' => 'value2'))); ?>
// Output:
// http://project/controller/action/param2/value2/?param1=value1
class Zx_View_Helper_MyUrl
{
    public function myUrl(&$url, &$toAdd = array())
    {
        $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $query = parse_url($requestUri, PHP_URL_QUERY);
        if($query == '')
        {
            return $url;
        }
        else if(empty($toAdd)) // The code highlighter doubles the "empty" O.o
        {
            return $url . '/?' . $query;
        }
        else
        {
            $toAdd = (array)$toAdd;
            $query = explode("&", $query);

            $add = '/?';

            foreach($toAdd as $addPart)
            {
                foreach($query as $queryPart)
                {
                    if(strpos($queryPart, $addPart) !== False)
                    {
                        $add .= '&' . $queryPart;
                    }
                }
            }
            return $url . $add;
        }
    }
}