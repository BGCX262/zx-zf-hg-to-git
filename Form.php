<?php
/*
* Author: Aleksey Deryagin, Aleksey@Deryagin.ru
* @todo decorators?
*/
class Zx_Form extends Zend_Form
{
	const STAR = '<span class="star">*</star>';

    protected $elementDecorators = array(
		'table' => array(
			'ViewHelper',
			'Errors',
			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'formtdr')),
			array('Label', array('tag' => 'td', 'class' => 'formtdl')),
			array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'formtr')),
		),
	);

    protected $buttonDecorators = array(
		'table' => array(
			'ViewHelper',
			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'formtdr2')),
			array(array('label' => 'HtmlTag'), array('tag' => 'td', 'class' => 'formtdl2', 'placement' => 'prepend')),
			array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'formtr2')),
		),
    );

    protected $starDecorators = array(
		array('decorator' => array('br' => 'HtmlTag'), 'options' => array('tag' => '&nbsp;<div style="color: #CEB70D; display: inline;">*</div>', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	);

	protected $formDecorators = array(
		'table' => array(
			'FormElements',
			array('HtmlTag', array('tag' => 'table', 'class' => 'formtable')),
			'Form',
		)
	);

	protected $conf;
	protected $_msg = array();
	protected $_isDecorators = null;

	protected $_confElement = array(
		'default' => array('filters' => array('StripTags'), 'validators' => array('NotEmpty')),
		'digital' => array('filters' => array('StripTags', 'Digits'), 'validators' => array('Digits'))
	);


    public function __construct($options = null)
    {
		parent::__construct($options);

		$this->conf = Zend_Registry::get('conf');

		// use skipTranslator for 1-language sites
		if ( !empty($this->conf->skip_translate) || !empty($options['skipTranslator']) ) {
			$this->setTranslator(NULL);//we don't need translator for English!
		} else {
			$this->_loadTranslator();
		}

		if ($this->_isDecorators) {$this->setDecorators($this->formDecorators['table']);}
    }
	
	
	/**
	* @todo
	* @param
	* @return
	*/
/*
	public function loadDefaultDecorators()
	{
		$this->setDecorators($this->formDecorators['table']);
	}
*/

	protected function elementPassword($title, $label)
	{
		$el = new Zend_Form_Element_Password($title);
		$el->setLabel($label)->setRequired(true)->addFilter('StripTags')->addFilter('StringTrim')->addValidator('NotEmpty')->addValidator('StringLength', false, array(6));
		return $el;
	}
	
	
	protected function elementHidden($title)
	{
		$element = new Zend_Form_Element_Hidden($title);
		$element->removeDecorator('label');
		return $element;
	}


	protected function elementSelect($title, $options, $conf = null)
	{
		$element = new Zend_Form_Element_Select($title);
		if (is_array($options)) {
			$element->setOptions($options);
		} else {
			$element->setLabel($options);
		}
		return $element;
	}



	protected function elementText($title, $options = null, $conf = null)
	{
        $element = new Zend_Form_Element_Text($title);
		if (is_array($options)) {
			$element->setOptions($options);
		} elseif (!is_null($options)) {
			$element->setLabel($options);
		}

        $element->addFilter('StringTrim');

		if (!empty($conf['filters']))
		{
			$element->addFilters($conf['filters']);
		}

		if (!empty($conf['validators']))
		{
			$element->addValidators($conf['validators']);
		}

		return $element;
	}


	protected function elementEmail($name = 'email', $label = 'E-mail')
	{
		$el = $this->elementText($name, array(
				'label' => $label,
				'filters'  => array('StringToLower'),
				'validators'  => array('EmailAddress')
			))
			->setRequired(true);
		return $el;
	}



	/**
	* Zend_Form_Element_Textarea wrapper
	* @param string $title
	* @param string $label
	* @return Zend_Form_Element_Textarea
	*/
	protected function elementTextarea($title, $options = null)
	{
        $el = new Zend_Form_Element_Textarea($title);

		if (is_array($options))
		{
			$el->setOptions($options);
		} else {
			if (!empty($options))
			{
				$el->setLabel($options);
			}
		}

        $el->setRequired(true)->addFilter('StringTrim')->addValidator('NotEmpty');
		return $el;
	}



    /**
     * setTranslator() wrapper
     */
    protected function _loadTranslator()
	{
		// load global conf
		$translate = require PATH_MY . 'Zx/application/translate/main.php';

		// load project conf (optional)
		$translate_project = @include '../application/translate/main.php';

		if (!empty($translate_project)) {
			$translate = my_array_merge_recursive($translate, $translate_project);
		}

		if (!empty($translate))
		{
			//--< wrong way!
			#$conf = Zend_Registry::get('conf');
			#$translator = new Zend_Translate_Adapter_Array($translate, $conf->translate->locale);//@todo Zend_Locale
			//-->

			// Zend_Locale
 			$locale = Zend_Registry::get('Zend_Locale');#d($locale);
			$translator = new Zend_Translate_Adapter_Array($translate, $locale);#d($translator);
			$this->setTranslator($translator);
		}
    }


	/**
	* Get message
	* @param
	* @return
	*/
	function msg($key)
	{
		if (!empty($this->_msg[$key])) {
			return $this->_msg[$key];
		} else {
			return FrontEnd::getMsg($key);
		}
		return null;
	}
	

	/**
	* addElements wrapper
	* @param
	* @return
	*/	
	protected function _addElements($aElements)
	{
		foreach ($aElements as $element)
		{
			// Добавление звездочки для обязательных полей
			if ($element->isRequired()) {
				if ($this->_isDecorators) {
					$element->addDecorators($this->starDecorators);
				}
			}
		}
		$this->addElements($aElements);
	}
}