<?php
class Zx_Db_Table_Row_Users extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_Rowset_Users extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Users extends Zx_Db_Table
{
    protected $_name = 'users';

	protected $_identityColumn = 'username';
	protected $_credentialColumn = 'password';
	
	protected $_rowClass = 'Zx_Db_Table_Row_Users';
	protected $_rowsetClass = 'Zx_Db_Table_Rowset_Users';

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