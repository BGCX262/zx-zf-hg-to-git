<?php
/**
* http://snowcore.net/%d0%bd%d0%b0%d0%bf%d0%b8%d1%81%d0%b0%d0%bd%d0%b8%d0%b5-%d1%81%d0%be%d0%b1%d1%81%d1%82%d0%b2%d0%b5%d0%bd%d0%bd%d0%be%d0%b3%d0%be-view-helper-truncate
* http://zendframework.ru/forum/index.php?topic=1212.0
* В Smarty есть модификатор, который отрезает часть текста - truncate. Его можно использовать, например при выводе анонса новостей.
* Создадим и мы helper, который реализует данный функционал.
*/
class Zx_View_Helper_Truncate extends Zend_View_Helper_Abstract
{
    public function truncate($string, $length = 50, $postfix = '')
    {
        $truncated = trim($string);
        $length = (int)$length;
        if (!$string) {
            return $truncated;
        }
        $fullLength = iconv_strlen($truncated, 'UTF-8');
        if ($fullLength > $length) {
            $truncated = trim(iconv_substr($truncated, 0, $length, 'UTF-8')) . $postfix;
        }
        return $truncated;
    }
}