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
	function sname($br = false, $nbsp = false)
	{
		$s = $nbsp ? '&nbsp;' : ' ';
		return $this->name . ($br ? '<br/>' : $s) . $this->surname;
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

	/**
	 * Генерация служебной информации (IP, UA, TS)
	 */
	/*protected!*/ function _addLast(&$data)
	{
		$r = Zend_Controller_Front::getInstance()->getRequest();

		$data['last_ip'] = new Zend_Db_Expr("INET_ATON('" . $r->getServer('REMOTE_ADDR') . "')");

		$ip2 = $r->getServer('HTTP_X_FORWARDED_FOR');
		if (!empty($ip2)) {
			if (substr_count($ip2, '.') >= 4) { //check for double IP
				$a = explode(',', $ip2);
				$data['last_ip2'] = new Zend_Db_Expr("INET_ATON('" . $a[0] . "')");
			} else {
				$data['last_ip2'] = new Zend_Db_Expr("INET_ATON('" . $ip2 . "')");
			}
		}

		$data['last_ua'] = $r->getServer('HTTP_USER_AGENT');
		$data['last_ts'] = new Zend_Db_Expr('NOW()');

		return true;
	}

	/**
	 * User is authorized
	 * @return boolean
	 */
	static function imOk()
	{
		$auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity())
		{
			$identity = $auth->getIdentity();
			return ($identity && $identity->id);
		} else {
			return false;
		}
	}
	
	/**
	 * If user_id is current user id
	 * @param integer $id 
	 */
	static function itsMe($id)
	{
		$auth = Zend_Auth::getInstance();
		$identity = $auth->getIdentity();
		
		return ($identity && $identity->id == $id);
	}
	

}