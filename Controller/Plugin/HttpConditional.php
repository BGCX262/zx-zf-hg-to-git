<?php
/**
* http://smartycode.com/performance/zend-framework-browser-caching/
* http://lobach.info/develop/zf/enable-conditional-get-in-zend-framework-app/
* Plugin to support conditional GET for php pages (using ETag)
* Should be loaded the very last in the plugins stack
*
* @author $Author: danila $
* @version $Id: Conditional.php 15741 2009-02-08 11:58:44Z danila $
*
*/
#class Smartycode_Http_Conditional extends Zend_Controller_Plugin_Abstract
class Zx_Controller_Plugin_HttpConditional extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        $send_body = true;

    $etag = '"' . md5($this->getResponse()->getBody()) . '"';

    $inm = split(',', getenv("HTTP_IF_NONE_MATCH"));

    $inm = str_replace('-gzip', '', $inm);

    // TODO If the request would, without the If-None-Match header field,
    // result in anything other than a 2xx or 304 status,
    // then the If-None-Match header MUST be ignored

    foreach ($inm as $i) {
      if (trim($i) == $etag) {
        $this->getResponse()
            ->clearAllHeaders()
            ->setHttpResponseCode(304)
            ->clearBody();
        $send_body = false;
        break;
      }
    }

    $this->getResponse()
      ->setHeader('Cache-Control', 'max-age=7200, must-revalidate', true)
      ->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 2 * 3600) . ' GMT', true)
      ->clearRawHeaders();

    if ($send_body) {
      $this->getResponse()
		  ->setHeader('Content-Length', strlen($this->getResponse()->getBody()));
    }

    $this->getResponse()->setHeader('ETag', $etag, true);
    $this->getResponse()->setHeader('Pragma', '');

    }
}