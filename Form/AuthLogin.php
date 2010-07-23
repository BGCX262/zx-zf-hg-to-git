<?php
/**
* Auth Login Form
*/
class Zx_Form_AuthLogin extends Zx_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

		$this->_msg = $this->conf->msg->auth->toArray();

        $this->setName('login');
		$this->setMethod('POST');
		$this->setAction('/auth/login/');

        $e['username'] = new Zend_Form_Element_Text('username');
        $e['username']
			->setLabel($this->conf->msg->auth->login)
        	->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('NotEmpty')
			;

        $e['password'] = new Zend_Form_Element_Password('password');
        $e['password']
			->setLabel($this->conf->msg->auth->password)
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('NotEmpty')
			;
/*
		$referer = Zend_Controller_Front::getInstance()->getRequest()->getServer('HTTP_REFERER');
		$host = Zend_Controller_Front::getInstance()->getRequest()->getServer('HTTP_HOST');
		if ($referer && (strpos($referer, 'http://' . $host) !== false) )
		{
			l($referer, 'referer', Zend_Log::DEBUG);
			$e['referer'] = new Zend_Form_Element_Hidden('referer');
			$e['referer']->setValue($referer);
		}
*/
		$e['referer'] = $this->elementHidden('referer');

        $e['submit'] = new Zend_Form_Element_Submit('submit');
        $e['submit']->setAttrib('id', 'submitbutton')
			->setLabel('Войти')
			->removeDecorator('Label')
			;

        $this->addElements($e);
    }
}