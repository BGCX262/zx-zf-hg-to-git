<?php
/**
* Модель набора записей (rowset) БД
* @author Дерягин Алексей (aleksey@deryagin.ru)
* @version 4/28/2009
*/
class Zx_Db_Table_Rowset extends Zend_Db_Table_Rowset_Abstract
{
	public function getFirst()
	{
		return $this->getRow(0);
	}
	
	public function getRange($start, $end)
	{
		return array_map(array($this, 'getRow'), range($start, $end));
	}
}
