<?php
/**
* http://zfsite.andreinikolov.com/2008/05/part-4-zend_form-captcha-password-confirmation-date-selector-field-zend_translate/
*/
#require_once 'Zend/Validate/Abstract.php';
class Zx_Validate_PasswordConfirmation extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'passwordConfirmationNotMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Passwords do not match'
    );

    protected $fieldToMatch = '';

    public function __construct($fieldToMatch)
    {
        $this->fieldToMatch = $fieldToMatch;
				#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->fieldToMatch, 1) . "</textarea><br>";die;
    }
    
		public function isValid($value, $context = null)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);
				
        if (!isset($context[$this->fieldToMatch]) || $context[$this->fieldToMatch] !== $valueString)
        {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}

// jEdit :indentSize=4:tabSize=4:noTabs=false:lineSeparator=\n:mode=php: