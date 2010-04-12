<?php
/**
* Parameters values model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 8/15/2009
*/
class Zx_Db_Table_Parameters_Values extends Zx_Db_Table#Zend_Db_Table_Abstract
{
    protected $_name = 'p_values';

	/**
	* Получить все параметры и их значения
	* @param integer $itemId
	* @param string $where
	* @return array
	*/
	function getParams($itemId, $where = '')
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from(array('p' => $this->_name))
			->join(array('c' => 'p_list'), 'c.id = p.p_id', array('ps_id', 'pt_id', 'price', 'code', 'title', 'unit', 'txt'))
			#->join(array('s' => 'p_sets'), 'p.value = s.id', array('title AS stitle'))
			->where('p.i_id = ?', $itemId);

		if (!empty($where)) {
			$select = $select->where($where);
		}

		$select = $select->order('c.flag_order');

		$res = $this->fetchAll($select); //Zend_Db_Table_Rowset

		return $res;
	}
}