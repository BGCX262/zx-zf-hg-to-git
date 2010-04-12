<?php
class Zx_Db_Table_Users extends Zx_Db_Table
{
    protected $_name = 'users';

	protected $identityColumn = 'username';
	protected $credentialColumn = 'password';
	
/*
	function init()
	{
		parent::init();

		$this->_data = array
		(
			'identityColumn' => $this->identityColumn,
			'credentialColumn' => $this->credentialColumn,
		);
	}
*/

	function getIdentityColumn()
	{
		return $this->identityColumn;		
	}

	function getCredentialColumn()
	{
		return $this->credentialColumn;		
	}
}