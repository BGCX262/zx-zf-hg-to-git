<?php
/**
* Articles model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 5/20/2009
*/
class Zx_Db_Table_ArticlesRow extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_ArticlesRowset extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Articles extends Zx_Db_Table
{
	protected $_name = 'content2';
	protected $_primary = 'id';
	protected $_rowClass = 'Zx_Db_Table_ArticlesRow';
	protected $_rowsetClass = 'Zx_Db_Table_ArticlesRowset';
	
	
	/**
	* NLS parameters for SQL selects
	* @var
	*/
/* 	protected $aNLS = array(
		'name' => 'content2_nls',
		'cols' => 'c.*',
	);
 */	
	#protected $debug = true;
}