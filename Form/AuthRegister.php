<?php
/**
* @todo decorators
*/
class Zx_Form_AuthRegister extends Zx_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        
		$this->setName('form_register');
		$this->setAction('/auth/register');
		$this->setMethod('post');
		#$ru = array('Value is empty, but a non-empty value is required' => 'message1');
		#$translate = new Zend_Translate('array', $ru, 'ru');
		#$translate = include(APP_DIR . DIRECTORY_SEPARATOR . 'translation.php');
		#$translator = new Zend_Translate_Adapter_Array($translate);				

		$a = array();
		
		$a['surname'] = $this->getElement_Text('surname', 'Фамилия');
		#$a['surname']->setTranslator($translate);
		
		$a['name'] = $this->getElement_Text('name', 'Имя');
		$a['middlename'] = $this->getElement_Text('middlename', 'Отчество');
		
		$a['password'] = $this->getElement_Password('password', 'Пароль');
		$password2 = $this->getElement_Password('password2', 'Подтверждение пароля');
		$password2->addValidator(new My_Validate_PasswordConfirmation('password'));
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($password2, 1) . "</textarea><br>";die;
		$a['password2'] = $password2;

		#$captcha	 = new Zend_Form_Element_Captcha();
		$a['captcha'] = new Zend_Form_Element_Captcha('captcha', array(
				'label' => 'Пожалуйста, введите символы на иллюстрации:',
				'captcha' => array(
					'captcha' => 'Image',
					'wordLen' => 6,
					'timeout' => 300,
					'font' => FrontEnd::getFont(),
					#'font' => $this->'arial', // must be in GDFONTPATH $_ENV
					'imgurl' => '/images/captcha',
				),
		));
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($a['captcha'], 1) . "</textarea><br>";die;
				
        $id = new Zend_Form_Element_Hidden('id');
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');
		$submit->setLabel('Зарегистрироваться');
		#$this->addElement('submit', 'login', array('label' => 'Login'));

		$aa = array_values($a);
		$aaa = array($id, $submit);
		$this->addElements(array_merge($aa, $aaa));
    }
}