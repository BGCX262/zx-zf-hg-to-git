<?php
/**
* Votes model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @since 26.07.2009
*/
class Zx_Db_Table_VotesRow extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_VotesRowset extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Votes extends Zx_Db_Table
{
	protected $_name = 'votes';
	protected $_primary = 'id';
	protected $_rowClass = 'Zx_Db_Table_VotesRow';
	protected $_rowsetClass = 'Zx_Db_Table_VotesRowset';

	protected $_referenceMap = array
	(
		'content' => array(
			'columns'		=> array('pid'),
			'refTableClass'	=> 'content',
			'refColumns'	=> array('id')
		),
		'content2' => array(
			'columns'		=> array('pid'),
			'refTableClass'	=> 'content2',
			'refColumns'	=> array('id')
		),
		'c_positions' => array(
			'columns'		=> array('pid'),
			'refTableClass'	=> 'c_positions',
			'refColumns'	=> array('id')
		),
		'users' => array(
			'columns'		=> array('user_id'),
			'refTableClass'	=> 'users',
			'refColumns'	=> array('id')
		)
	);


	/**
	 * Проверка на голосование!
	 * @param array $data
	 * @return boolean
	 */
	function isVoted($data)
	{
		if (empty($data['user_id'])) {
			return false;
	  	}

		$select = $this->select()
			->from($this->_name, 'value')
			->where('pid=?', $data['pid'])
			->where('sid=?', $data['sid'])
			->where('user_id=?', $data['user_id']);

		$row = $this->fetchRow($select);

		if ($row) {
			return $row->value;
		} else {
			return false;
		}
	}


	/**
	 * Проверка на голосование!
	 * @param array $data
	 * @return boolean
	 */
	function countVotes($data)
	{
		if ( empty($data['pid']) || empty($data['sid']) ) {return false;}

		$select = $this->select()
			->from($this->_name, 'AVG(value) AS avg, COUNT(*) AS cnt')
			->where('pid=?', $data['pid'])
			->where('sid=?', $data['sid']);

		l($select);
		$row = $this->fetchRow($select);

		if ($row) {
			return array('avg' => $row->avg, 'cnt' => $row->cnt);
		} else {
			return false;
		}
	}


	function updateData($data)
	{
		if ( empty($data['pid']) || empty($data['sid']) || empty($data['value']) ) {return false;}

		$auth = Zend_Auth::getInstance();
		$identity = $auth->getIdentity();
		if ( !$identity ) {return false;}

		$data['user_id'] = $identity->id;

		$this->addTechInfo($data, array('info' => true));

		$res = $this->_updateData($data, array('notify' => false, 'upload' => false));#, array('test' => 1)

		if ($res) {
			// update stat via TRIGGER!
			// http://www.wddx.ru/2010/07/count-vs.html
			$a = $this->countVotes($data);
			if ($a) {
				switch ($data['sid']) {
				case 3:
					$positions = new Positions();
					$update = array(
						'rating' => $a['avg'],
						'votes' => $a['cnt']
					);
					#l($data);
					// UPDATE c_positions SET rating = '3.5000', votes = 2 WHERE id = 3;
					#$where = $this->getAdapter()->quoteInto('id = ?', $data['pid']);
					$res = $positions->update($update, 'id=' . $data['pid']);
					break;
				}
			}
		}

		return $res;
	}

}