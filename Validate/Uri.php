<?php
class Zx_Validate_Uri extends Zend_Validate_Abstract
{
    const NOT_URI = 'notUri';

    protected $_messageTemplates = array(
        self::NOT_URI => 'Некорректная www-ссылка.'
    );
    
    public function isValid($value)
    {
		$valid = Zend_Uri::check($value);

		if ($valid) {
			return true;
		} else {
			$this->_error(self::NOT_URI);
			return false;
		}
    }
}