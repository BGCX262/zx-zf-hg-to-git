<?php
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

		#$a['username'] = $this->elementText('username', 'Логин');
		
		$a['surname'] = $this->elementText('surname', 'Фамилия');
		#$a['surname']->setTranslator($translate);
		
		$a['name'] = $this->elementText('name', 'Имя');
		$a['middlename'] = $this->elementText('middlename', 'Отчество');
		
		$a['password'] = $this->elementPassword('password', 'Пароль');
		$password2 = $this->elementPassword('password2', 'Подтверждение пароля');
		$password2->addValidator(new Zx_Validate_PasswordConfirmation('password'));
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($password2, 1) . "</textarea><br>";die;
		$a['password2'] = $password2;

		#$captcha	 = new Zend_Form_Element_Captcha();
/*
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
 */
				
        $id = $this->elementHidden('id');
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');
		$submit->setLabel('Зарегистрироваться');
		#$this->addElement('submit', 'login', array('label' => 'Login'));

		$aa = array_values($a);
		$aaa = array($id, $submit);
		$this->addElements(array_merge($aa, $aaa));
    }
}