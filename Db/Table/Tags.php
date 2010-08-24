<?php
/**
* Tags model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @since 8/12/2010
*/
class Zx_Db_Table_Tags_Row extends Zx_Db_Table_Row
{
	function getData()
	{
		return $this->title;
	}
}

class Zx_Db_Table_Tags_Rowset extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Tags extends Zx_Db_Table#Zend_Db_Table_Abstract
{
    protected $_name = 'tags';
	protected $_rowClass = 'Zx_Db_Table_Tags_Row';
	protected $_rowsetClass = 'Zx_Db_Table_Tags_Rowset';

	protected $_dependentTables = array('Zx_Db_Table_Tags_Values');

	/**
	* Получить все тэги и их значения
	* @param row $row
 	* @param int $type
	* @param string $where
	* @return array
	*/
	function getTags($row, $conf)
	{
		$type = !empty($conf['type']) ? $conf['type'] : 0;

		$select = $this->select()
			->setIntegrityCheck(false)
			->from(array('p' => 'tags_values'), array('tid'))
			->join(array('c' => $this->_name), 'c.id = p.tid', array('title'))
			->where('p.pid = ?', $row->id)
			->where('p.flag_type = ?', $type);

		if (!empty($conf['where'])) {
			$select = $select->where($where);
		}

		#$select = $select->order('c.flag_order');

		$res = $this->fetchAll($select); //Zend_Db_Table_Rowset

		if (!empty($conf['array'])) {
			$res = $res->toArray();
		}

		return $res;
	}

}