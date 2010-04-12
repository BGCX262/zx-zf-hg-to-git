<?php
/**
* @todo decorators
*/
class Zx_Form_AuthLogin extends Zx_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $formDecorators = array('HtmlTag', array('tag'=>'table'));
		#$this->addDecorators($formDecorators);

		$elementDecorators = array(
			'ViewHelper',
			'Errors',
			array('decorator' => array('br' => 'HtmlTag'), 'options' => array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
			array('decorator' => array('tdOpen' => 'HtmlTag'), 'options' => array('tag' => 'td', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
			array('decorator' => array('tdClose' => 'HtmlTag'), 'options' => array('tag' => 'td', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
			array('decorator' => array('label' => 'Label'), 'options' => array('separator' => ' ')),
			array('decorator' => array('mainCell' => 'HtmlTag'), 'options' => array('tag' => 'td', 'class' => 'tregleft', 'valign' => 'top')),
			array('decorator' => array('mainRowClose' => 'HtmlTag'), 'options' => array('tag' => 'tr'))
		);

		$this->_msg = $this->conf->msg->auth->toArray();

        $this->setName('login');
		$this->setMethod('POST');
		$this->setAction('/auth/login/');

        $username = new Zend_Form_Element_Text('username');
        $username
			->setLabel($this->conf->msg->auth->login)
        	->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('NotEmpty')
			#->setDecorators($elementDecorators)
			#->removeDecorator('Label')
			;

        $password = new Zend_Form_Element_Password('password');
        $password
			->setLabel($this->conf->msg->auth->password)
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('NotEmpty')
			#->setDecorators($elementDecorators)
			#->removeDecorator('Label')
			;

		$referer = new Zend_Form_Element_Hidden('referer');
		if (!empty($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], 'auth/login') === false) ) {
			$referer->setValue($_SERVER['HTTP_REFERER']);
		}

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton')
			->setLabel('Войти')
			#->clearDecorators()
			#->setDecorators($elementDecorators)
			->removeDecorator('Label')
			;
		#$submit->loadDefaultDecorators();

        $this->addElements(array($username, $password, $referer, $submit));
    }
}