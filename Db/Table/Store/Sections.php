<?php
class Zx_Db_Table_Row_Store_Sections extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_Rowset_Store_Sections extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Store_Sections extends Zx_Db_Table
{
	protected $_name = 'c_sections';
    protected $_rowClass = 'Zx_Db_Table_Row_Store_Sections';
    protected $_rowsetClass = 'Zx_Db_Table_Rowset_Store_Sections';

	/**
	* NLS parameters for SQL selects
	* @var
	*/
/* 	protected $aNLS = array(
		'name' => 'c_sections_nls',
		'cols' => 'c.*',
	);
 */	

	function init()
	{
		parent::init();

		$this->imgs['folder'] = 'sections';
	}
}