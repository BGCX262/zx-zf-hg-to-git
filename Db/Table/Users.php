<?php
class Zx_Db_Table_Users extends Zx_Db_Table
{
    protected $_name = 'users';

	protected $_identityColumn = 'username';
	protected $_credentialColumn = 'password';

/*
	function init()
	{
		parent::init();

		$this->_data = array_merge($this->_data, array
		(
			#'isPaginator' => false,
			#'identityColumn' => $this->identityColumn,
			#'credentialColumn' => $this->credentialColumn,
		));
	}
*/

	function getIdentityColumn()
	{
		return $this->_identityColumn;
	}

	function getCredentialColumn()
	{
		return $this->_credentialColumn;
	}
}