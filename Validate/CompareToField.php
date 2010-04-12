<?php
#http://www.zfforums.com/zend-framework-general-discussions-1/general-q-zend-framework-2/need-help-zend_form-723.html
class Zend_Validate_CompareToField extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Fields do not match.'
        );
    
    protected $_field;

    public function __construct($field = null)
    {
        $this->setField($field);
    }

    public function getField()
    {
        return $this->_field;
    }

    public function setField($field)
    {
        $this->_field = $field;
        return $this;
    }

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        if (is_array($context)) {
            if (isset($context[$this->_field])
                && ($value == $context[$this->_field]))
            {
                return true;
            }
        } elseif (is_string($context) && ($value == $context)) {
            return true;
        }

        $this->_error(self::NOT_MATCH);
        return false;
    }
}
?>