<?php
class Zx_Form_Contact extends Zx_Form
{
    public function __construct($options = null)
    {
		parent::__construct($options);
		$this->setName('form_contacts');
		$this->setMethod('post');

		$this->addElement($this->elementHidden('id'));

		if (empty($options['skip_person']))
		{
			$e['person'] = $this->elementText('person', array(
				'required' => true,
                'decorators' => $this->elementDecorators['table'],
                'label' => $this->conf->msg->feedback->person,
            ));
		}

/* 		if (empty($options['skip_address']))
		{
			$e['address'] = $this->elementText('address', array(
				'required' => true,
                'decorators' => $this->elementDecorators['table'],
                'label' => $this->conf->msg->feedback->address,
            ));
		}
*/
		if (empty($options['skip_email']))
		{
			$e['email'] = $this->elementText('email', array(
				'required' => true,
                'decorators' => $this->elementDecorators['table'],
                'label' => $this->conf->msg->feedback->email,
            ));
		}

		if (empty($options['skip_phone']))
		{
			$e['phone'] = $this->elementText('phone', array(
				#'required' => true,
                'decorators' => $this->elementDecorators['table'],
                'label' => $this->conf->msg->feedback->phone,
            ));
		}

		if (empty($options['skip_subject']))
		{
			$e['subject'] = $this->elementText('subject', array(
				#'required' => true,
                'decorators' => $this->elementDecorators['table'],
                'label' => $this->conf->msg->feedback->subject,
            ));
		}

		// message (textarea)
		$e['txt'] = $this->elementTextarea('txt', $this->conf->msg->feedback->txt)
			->setAttrib('rows', 5)
			->setAttrib('cols', 50)
			->setDecorators($this->elementDecorators['table']);

		// submit
		$element = new Zend_Form_Element_Submit('submit');
		$element->setAttrib('id', 'submitbutton')
			->setLabel($this->conf->msg->feedback->submit)
			->setDecorators($this->buttonDecorators['table']);
		$e['submit'] = $element;

		$this->_addElements($e);
	}
}