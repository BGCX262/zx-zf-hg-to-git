<?php
/**
* Parameters list model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 8/15/2009
*/
class Zx_Db_Table_Parameters_ListRow extends Zx_Db_Table_Row
{
	function getData()
	{
		return $this->title;
	}
}

class Zx_Db_Table_Parameters_ListRowset extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Parameters_List extends Zx_Db_Table#Zend_Db_Table_Abstract
{
    protected $_name = 'p_list';
	protected $_rowClass = 'Zx_Db_Table_Parameters_ListRow';
	protected $_rowsetClass = 'Zx_Db_Table_Parameters_ListRowset';
	#protected $_dependentTables = array('Zx_Db_Table_PollData', 'Zx_Db_Table_PollLog');
    
}