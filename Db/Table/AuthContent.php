<?php
/**
* AuthContent model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @since 4/16/2011
*/

#class Zx_Db_Table_Tags_Row extends Zx_Db_Table_Row{}
#class Zx_Db_Table_Tags_Rowset extends Zx_Db_Table_Rowset{}

class Zx_Db_Table_AuthContent extends Zx_Db_Table#Zend_Db_Table_Abstract
{
  protected $_name = 'auth_content';
	#protected $_rowClass = 'Zx_Db_Table_Tags_Row';
	#protected $_rowsetClass = 'Zx_Db_Table_Tags_Rowset';
	#protected $_dependentTables = array('Zx_Db_Table_Tags_Values');

	protected $_roles = array(
		0 => 'Все посетители',
		1 => 'Только участники',
		2 => 'Только VIP',
		3 => 'Только администрация',
		9 => 'Индивидуальная настройка'
	);

	function loadRoles() {
		return $this->_roles;
	}

	/**
	* Check user access right to content item
	* @param Zx_Db_Table_Row $row
 	* @param null|object $identity
	* @param int $sid
	* @return boolean
	*/
	function check($row, $identity, $sid = 1)
	{
		$pid = $row->id;
		$role = $row->auth;

		if (!$role) {return true;}
		if ($role && !$identity) {return false;}

		if ($role != 9) {
			if ($role > $identity->type_id) {return false;} // превышение полномочий
			return true;
		} else { // Индивидуальная настройка
			$select = $this->select()
				->from(array('p' => 'auth_content'), array('id'))
				->where('user_id = ?', $identity->id)
				#->where('user_id = 8')
				->where('sid = ?', $sid)
				->where('pid = ?', $pid);
			#d($select);

			$row = $this->fetchRow($select);
			if ($row) {return true;}
		}
		return false;
	}

}