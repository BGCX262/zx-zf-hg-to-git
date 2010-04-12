<?php
/*
@todo
http://groups.google.ru/group/ru-zend-framework/msg/4106d01328061dae
http://habrahabr.ru/blogs/zend_framework/42748/
*/
class Zx_Form_Decorator_GroupDecorator extends Zend_Form_Decorator_Abstract {
    /**
     * Items (decorators) to group
     * @var array
     */
    protected $_items = null;

    /**
     * Temporary form to perform decorators operations
     * @var Zend_Form_Element
     */
    private $_temporaryDecoratorsContainer = null;

    /**
     * Constructor
     *
     * @param array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null) {
        parent::__construct($options);
        $this->_temporaryDecoratorsContainer = new
Zend_Form_Element('_temporaryDecoratorsContainer',
array('DisableLoadDefaultDecorators' => true));
        $this->getItems();
    }

    /**
     * Set items to use
     *
     * @param array $items
     * @return Zend_Form_Decorator_GroupDecorator
     */
    public function setItems($items) {
        $this->_items = $this->_temporaryDecoratorsContainer-
>clearDecorators()->addDecorators($items)->getDecorators();

        return $this;
    }

    /**
     * Get tag
     *
     * If no items is registered, either via setItems() or as an
option, uses empty array.
     *
     * @return array
     */
    public function getItems() {
        if (null === $this->_items) {
            if (null === ($items = $this->getOption('items'))) {
                $this->setItems(array());
            } else {
                $this->setItems($items);
                $this->removeOption('items');
            }
        }
        return $this->_items;
    }

    public function addDecorator($decorator, $options = null) {
        $this->_temporaryDecoratorsContainer->addDecorator($decorator,
$options);
        return $this;
    }

    public function clearDecorators() {
        $this->_temporaryDecoratorsContainer->clearDecorators();
        $this->_items = array();
    }

    public function getDecorator($index = null) {
        if (null === $index) {
            return $this->_items;
        }
        if (is_numeric($index)) {
            $_items = array_values($this->_items);
            return ($index < count($_items))?$_items[$index]:null;
        }
        if (is_string($index)) {
            return (array_key_exists($index, $this->_items))?$this-
>_items[$index]:null;

        }
        return null;
    }

    public function insertDecoratorBefore($index, $decorator, $options= null) {
        $_decoratorsToAdd = $this->_temporaryDecoratorsContainer->clearDecorators()->addDecorator($decorator, $options)->getDecorators();

        if (is_string($index)) {
            $index = array_search($index, array_keys($this->_items));
        }
        if (false !== $index) {
            $first = ($index > 0)?array_slice($this->_items, 0,
$index, true):array();
            $last = ($index < count($this->_items))?array_slice($this-
>_items, $index, null, true):array();

            $this->_items = array_merge($first, (array)
$_decoratorsToAdd, $last);
        }
        return $this;
    }

    /**
     * Render content wrapped in a group of decorators
     *
     * @param string $content
     * @return string
     */
    public function render($content) {
        $placement = $this->getPlacement();
        $items = $this->getItems();
        $_content = '';
        foreach ($items as $_decorator) {
            if ($_decorator instanceOf Zend_Form_Decorator_Interface)
{
                $_decorator->setElement($this->getElement());
                $_content = $_decorator->render($_content);
            }
            else {
                require_once 'Zend/Form/Decorator/Exception.php';
                throw new Zend_Form_Decorator_Exception('Invalid
decorator '.$_decorator.' provided; must be string or
Zend_Form_Decorator_Interface');
            }
        }
        switch ($placement) {
            case self::APPEND:
                return $content . $_content;
                break;
            case self::PREPEND:
                return $_content . $content;
                break;
            default:
                return $_content.$content.$_content;
                break;
        }
    }

}