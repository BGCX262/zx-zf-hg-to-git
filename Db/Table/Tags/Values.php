<?php
/**
* Tags values model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @since 8/12/2010
*/
class Zx_Db_Table_Tags_Values extends Zx_Db_Table#Zend_Db_Table_Abstract
{
    protected $_name = 'tags_values';

	/**
	 *
	 */
	function getIdsWhere($conf = array())
	{
		$type = !empty($conf['type']) ? $conf['type'] : 0;
		$prefix = !empty($conf['prefix']) ? $conf['prefix'] . '.' : '';

		$select = $this->select()
			->from($this->_name, array('pid'));

		if (!empty($conf['where'])) {
			$select = $select->where($conf['where']);
		} else {
			if (is_array($type)) {
				$select = $select->where('flag_type IN (' . implode(', ', $type) . ')');
			} else {
				$select = $select->where('flag_type = ?', $type);
			}
		}

		$select = $select->group('pid');

		$res = $this->fetchAll($select)->toArray();
		if ($res) {
			$where = ' ' . $prefix . 'id IN (';
			foreach ($res as $a) {
				$where .= $a['pid'] . ', ';
			}
			$where .= '0)';
			return $where;
		} else {
			return false;
		}
	}
}