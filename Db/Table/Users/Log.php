<?php
class Zx_Db_Table_Row_Users_Log_Log extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_Rowset_Users_Log extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Users_Log extends Zx_Db_Table
{
	protected $_name = 'users_log';

	protected $_rowClass = 'Zx_Db_Table_Row_Users_Log';
	protected $_rowsetClass = 'Zx_Db_Table_Rowset_Users_Log';

	function init()
	{
		parent::init();
	}

	function insertData($conf = null)
	{
		$auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity())
		{
			return false;
		}
		$identity = $auth->getIdentity();

		if (empty($identity->id)) {return false;}
		
		$data['user_id'] = $identity->id;

		$r = Zend_Controller_Front::getInstance()->getRequest();

		if (is_null($conf)) {
			$data['type_id'] = 0;
		}

		$data2 = parent::_addTechInfo();

		$res = $this->insert(array_merge($data, $data2));
		return $res;
	}

}