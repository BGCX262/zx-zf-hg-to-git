<?php
class Zx_Db_Table_Row_Store_SectionsTree extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_Rowset_Store_SectionsTree extends Zx_Db_Table_Rowset
{
}

/**
* Catalogue sections model (nested sets)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 09/11/28
*/
class Zx_Db_Table_Store_SectionsTree extends Zx_Db_Table_Tree
{
	protected $_name = 'c_sections_tree';

	protected $_rowClass = 'Zx_Db_Table_Row_Store_SectionsTree';
    protected $_rowsetClass = 'Zx_Db_Table_Rowset_Store_SectionsTree';

	protected $_dependentTables = array('c_sections_data');

	function init()
	{
		parent::init();

		$this->imgs['folder'] = 'sections';
	}
}