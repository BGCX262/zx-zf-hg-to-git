<?php
/**
* Tree model (nested sets)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 5/7/2009
* @abstract
*/
class Zx_Db_Table_Tree extends Zx_Db_Table
{
	
	protected $treeArray = array();
	
	/**
	* Get record by id
	* @param integer $id
	* @return array
	*/
	function getById($id)
	{
		$select = $this->select()
		->setIntegrityCheck(false)
		->from(array('p' => $this->_name), array('id', 'parent_id', 'lft', 'rgt'))
		->join(array('c' => $this->_dependentTables[0]), 'c.node_id = p.id', array('title', 'code', 'txt'))
		->where('c.flag_status = 1 AND p.id = ' . $id)
		#->order('p.lft')
		#->limit(2)
		;

		#$stmt = $this->getAdapter()->query($select);
		#$res = $stmt->fetchAll();
		$row = $this->fetchAll($select);

		if ($row) {return $row[0];}
		return false;
	}


	/**
	* Получить всё дерево
	* @return array
	*/
	function getTree()
	{
		$select = $this->getAdapter()->select()
		->from(array('p' => $this->_name), array('id', 'parent_id'))
		->join(array('c' => $this->_dependentTables[0]), 'c.node_id = p.id', array('title', 'code', 'announce', 'txt'))
		->where('c.flag_status = 1 AND p.parent_id IS NOT NULL');

 		$res = $this->getAdapter()->fetchAll($select);

		foreach ($res as $k => $v)
		{
			$this->treeArray[$v->id] = $v;
			#$aa[$v->parent_id][$v->id] = $v;
		}
		return $this->treeArray;
	}
	
	
	/**
	* More flexible array structure
	* @param
	* @return
	* @static
	*/
	function getTreeParentsArray($a)
	{
		$aa = array();
		foreach ($a as $k => $v) {
			$aa[$v->parent_id][$v->id] = $v;
		}
		return $aa;
	}



	/**
	* Получить все непосредственные потомки родителя
	* @param integer $id
	* @param boolean $parent
	* @return array
	*/
	function getChildrenById($id = 1, $parent = false)#, $children = false
	{
		$select = $this->select()
		->setIntegrityCheck(false)
		->from(array('p' => $this->_name), array('id', 'parent_id'))
		->join(array('c' => $this->_dependentTables[0]), 'c.node_id = p.id', array('title', 'code', 'announce', 'txt'));

		if (is_numeric($id)) {
			$select = $select->where('c.flag_status = 1 AND p.parent_id = ' . $id);
		} else {
			$select = $select->where('c.flag_status = 1 AND p.parent_id IN (' . $id . ')');
		}

		if ($parent)
		{
			if (is_numeric($id)) {
				$select = $select->orWhere('p.id = ' . $id);
			} else {
				$select = $select->orWhere('p.id IN (' . $id . ')');
			}
		}

		$select = $select->order('p.lft')
		#->limit(2)
		;
		#d($select);

		#if ($children) {}
 		$res = $this->fetchAll($select); // Zend_Db_Table_Rowset

		$a = array();
		#if (!is_array($res)) {$res = $res->toArray();}
		foreach ($res as $k => $v) {$a[$v->id] = $v;}

		return $a;
	}

	/**
	* @usage $this->Sections->getTopParentById($this->view->item['section_id']);
	* @param integer $id
	* @return mixed
	*/
	function getTopParentById($id)
	{
		$row = $this->getById($id);
		if (!$row) {return false;}

		$select = $this->getAdapter()->select()
		->from(array('p' => $this->_name), array('id', 'parent_id'))
		->join(array('c' => $this->_dependentTables[0]), 'c.node_id = p.id', array('title', 'code', 'txt'))
		->where('c.flag_status = 1')
		->where('p.lft < ' . $row->lft)
		->where('p.rgt > ' . $row->rgt)
		->where('p.parent_id = 1');

		$row = $this->getAdapter()->fetchAll($select);#, null, Zend_Db::FETCH_ASSOC
		if ($row) {return $row[0];}
		return false;
	}

	/**
	*
	* @param
	* @return
	*/
	#protected function toArray() {}
}