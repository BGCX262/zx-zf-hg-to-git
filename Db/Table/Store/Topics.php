<?php
class Zx_Db_Table_Store_Topics extends Zx_Db_Table
{
	protected $_name = 'c_topics';
	
	/**
	* NLS parameters for SQL selects
	* @var
	*/
	protected $aNLS = array(
		'name' => 'c_topics_nls',
		'cols' => 'c.*',
	);
	
}