<?php
/**
* Catalogue positions model (nested sets)
* @author Алексей Дерягин (aleksey@deryagin.ru)
*/
#class Zx_Db_Table_Row_Store_Positions extends Zx_Db_Table_Row{}
#class Zx_Db_Table_Rowset_Store_Positions extends Zx_Db_Table_Rowset{}

class Zx_Db_Table_Store_Positions extends Zx_Db_Table
{
	protected $_name = 'c_positions';

	#protected $_rowClass = 'Zx_Db_Table_Row_Store_Positions';
	#protected $_rowsetClass = 'Zx_Db_Table_Rowset_Store_Positions';

/* 	function init()
	{
		parent::init();
		// get / set array
		#$this->_data = array();
	}
*/
	/**
	* Get positions by section
	* @param integer $id
	* @return array
	*/
	function getItemsBySection($id)
	{
		$select = $this->getAdapter()->select()
		->from(array('p' => $this->_name), array('id', 'flag_hot', 'flag_new', 'flag_sale', 'price', 'title', 'txt'))
		->where('p.section_id = ?', $id)
		->order('p.flag_order')
		#->limit(2)
		;

		$res = $this->getAdapter()->fetchAll($select);#, null, Zend_Db::FETCH_ASSOC

		return $res;
	}
}