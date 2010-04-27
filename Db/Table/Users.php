<?php
class Zx_Db_Table_Row_Users extends Zx_Db_Table_Row
{
	function url()
	{
		$router = Zend_Controller_Front::getInstance()->getRouter();
		return $router->assemble(array('id' => $this->id), 'user') . '/';
	}

	/**
	 * Surname + name
	 */
	function sname($br = false)
	{
		return $this->name . ($br ? '<br/>' : '&nbsp;') . $this->surname;
    }

}

class Zx_Db_Table_Rowset_Users extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Users extends Zx_Db_Table
{
	protected $_name = 'users';

	protected $_rowClass = 'Zx_Db_Table_Row_Users';
	protected $_rowsetClass = 'Zx_Db_Table_Rowset_Users';

	protected $_identityColumn = 'username';
	protected $_credentialColumn = 'password';

	function init()
	{
		parent::init();

/*
		$this->_data = array_merge($this->_data, array
		(
			'isPaginator' => false,
		));
 */
	}

	function getIdentityColumn()
	{
		return $this->_identityColumn;
	}

	function getCredentialColumn()
	{
		return $this->_credentialColumn;
	}
}