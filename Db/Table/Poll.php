<?php
/**
* Poll model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 6/17/2009
*/
class Zx_Db_Table_Row_Poll extends Zx_Db_Table_Row
{
	function getVotes()
	{
		return $this->votes;
	}
}

class Zx_Db_Table_Rowset_Poll extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Poll extends Zx_Db_Table
{
	protected $_name = 'polls';

	protected $_rowClass = 'Zx_Db_Table_Row_Poll';
	protected $_rowsetClass = 'Zx_Db_Table_Rowset_Poll';

	protected $_dependentTables = array('Zx_Db_Table_PollData', 'Zx_Db_Table_PollLog');

	/**
	* Fetch rows
	* @return mixed
	*/
	function getItems()
	{
		$select = $this->select()
			->where('flag_status = 1')
			->order('dt DESC');

		$rows = $this->fetchAll($select);
		return $rows;
	}


	/**
	* Fetch row by id
	* @return mixed
	*/
	function getItem($id, $conf = false)
	{
		if ($id) {
			$row = $this->fetchById($id);
		} else {
			$select = $this->select()
				->where('flag_status = 1')
				->order('dt DESC')
				->limit(1);
			$row = $this->fetchRow($select);
		}
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($row, 1) . "</textarea><br>";die;
		if (!$row) {return array('poll' => 0, 'data' => 0);}
		$data = $row->findDependentRowset('Zx_Db_Table_PollData');
		#$log = $row->findDependentRowset('Zx_Db_Table_PollLog', 'Poll');
		return array('poll' => $row, 'data' => $data);#, 'log' => $log
	}

	/**
	*/
	function getArchiveCount()
	{
		$select = $this->select()
			->from($this->_name, 'COUNT(id) AS cnt')
			->where('flag_status = 1')
			->where('dt_end IS NULL OR NOW() > dt_end')
			->order('dt DESC');
		$row = $this->fetchRow($select)->toArray();
		return $row['cnt'];
	}


	/**
	 *
	*/
	function getArchive()
	{
		$select = $this->select()
			->where('flag_status = 1')
			->where('dt_end IS NULL OR NOW() > dt_end')
			->order('dt DESC')
			->limit(10);
		$rows = $this->fetchAll($select);
		return $rows;
/*
		if (!$rows) {return false;}

		$a = array();
		foreach ($rows as $row) {
			$data = $row->findDependentRowset('Zx_Db_Table_PollData');
			$a[] = array('poll' => $row, 'data' => $data);
		}
		return $a;
 */
	}


	/**
	* @usage $rows = $this->Content->getTopicContent(3); (ZF_KO::MainController)
	* @param
	* @return Zend_Db_Table_Rowset
	*/
/*
	function getTopicContent($topicId = null, $conf = array())
	{
		$_conf = $this->confSQL('dtf', array('fields' => array('id', 'title', 'announce')));
		if (!empty($conf)) {
			$conf = array_merge($conf, $_conf);
		} else {
			$conf = $_conf;
		}
		$where = $this->getTopicWhere($topicId);

		$select = $this->getSelect($this->getTopicWhere($topicId), $conf);
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($select->__toString(), 1) . "</textarea><br>";die;
		$rows = $this->paginator($select);
		return $rows;
	}
*/

	/**
	*
	* @param
	* @return
	*/
	function vote($formData)
	{
		if ( empty($formData['poll_id']) || empty($formData['quest']) )
		{
			return 'Пожалуйста, будьте внимательны при участии в опросе. Необходимо указать как минимум 1 вариант ответа.';
		}
		$poll_id = $formData['poll_id'];

		$PollData = new Zx_Db_Table_PollData();
		$PollLog = new Zx_Db_Table_PollLog();

		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($formData, 1) . "</textarea><br>";
		foreach ($formData['quest'] as $data_id => $v)
		{
			// check poll data
			$select = $PollData->select()->where('poll_id = ?', $poll_id)->where('id = ?', $data_id);
			$row = $PollData->getRow($select);
			#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($row, 1) . "</textarea><br>";die;

			// check poll spamming
			$ips = Zx_FrontEnd::getIP();
			$select = $PollLog->select()->where('poll_id = ?', $poll_id)->where('data_id = ?', $data_id)->where('ip = ?', new Zend_Db_Expr("INET_ATON('" . $ips['ip'] . "')"));
			#echo "DEBUG:<br><textarea rows=10 cols=100>" . $select . "</textarea><br>";die;
			$row = $PollLog->getRow($select);

/* 			if ($row) {
				return 'Данный IP-адрес (' . $ips['ip'] . ') уже использовался при участии в опросе.';# Использование 1 IP-адреса возможно в течении 1 суток.
			}
*/
			// insert poll data
			$data = array(
				'poll_id' => $poll_id,
				'data_id' => $data_id,
				'ip' => new Zend_Db_Expr("INET_ATON('" . $ips['ip'] . "')")
				#'ip' => ip2long($ips['ip']),
			);
			if (!empty($ips['ip2'])) {
				$data['ip2'] = ip2long($ips['ip']);
			}
			$res = $PollLog->insert($data);
			#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($res, 1) . "</textarea><br>";

			// update poll data
			if ($res) {
				$data = array(
					'votes' => new Zend_Db_Expr('votes+1')
				);
				$res = $PollData->update($data, 'poll_id = "' . $poll_id . '" AND id = "' . $data_id . '"');

				// update poll
				if ($res) {
					$res = $this->update($data, 'id = ' . $poll_id);
				}
			}
		}
		return true;
	}

	/**
	* @todo?
	* @return
	*/
	function getVotes($data)
	{
		$votes = array();
		foreach ($data as $v) {
		}
	}

	/**
	* Voters (by IP)
	* @return
	*/
	function getVoters()
	{
	}
}