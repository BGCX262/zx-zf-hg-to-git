<?php
class Zx_Form_Contact extends Zx_Form
{
    public function __construct($options = null)
    {
		parent::__construct($options);
		
		$this->setName('form_contacts');
		#$this->setAction('/auth/register');
		$this->setMethod('post');
		
/* 		$this->addElement('text', 'username', array(
			'label' => 'test',
			'validators' => array(
				'alnum',
				array('regex', false, '/^[a-z]/i')
			),
			'required' => true,
			'filters'  => array('StringToLower'),
		));
 */
 
		#$conf = Zend_Registry::get('conf');

		$e['id'] = new Zend_Form_Element_Hidden('id');
		
		if (empty($options['skip_person'])) {
			$e['person'] = $this->elementText('person', $this->conf->msg->feedback->person);
		}
		
		if (empty($options['skip_phone'])) {
			$e['phone'] = $this->elementText('phone', $this->conf->msg->feedback->phone);
		}
		
		if (empty($options['skip_email'])) {
			$e['email'] = $this->elementText('email', $this->conf->msg->feedback->email);
		}
		
		$e['txt'] = $this->elementTextarea('txt', $this->conf->msg->feedback->txt);
		$e['txt']->setAttrib('rows', 5);
		$e['txt']->setAttrib('cols', 50);
		#$a = $e['txt']->getAttribs();
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($a, 1) . "</textarea><br>";die;
		
		$e['submit'] = new Zend_Form_Element_Submit('submit');
		$e['submit']->setAttrib('id', 'submitbutton');
		$e['submit']->setLabel($this->conf->msg->feedback->submit);
		
		$this->addElements($e);
    }
	
}