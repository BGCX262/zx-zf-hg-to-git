<?php
class Zx_Db_Table_Row_Comment extends Zx_Db_Table_Row
{
	function getServiceName()
	{
		return $this->getTable()->getServiceName($this->service_id);
	}

	function getServiceById()
	{
		return $this->getTable()->getServiceById($this->service_id);
	}

	function countComments($sid, $where = null)
	{
/*
		if (Zend_Registry::isRegistered('countComments')) {
			$a = Zend_Registry::get('countComments');
			if (!empty($a[$this->item_id][$this->service_id])) {
				l($a[$this->item_id][$this->service_id], __METHOD__ . ' registry', Zend_Log::DEBUG);
				return $a[$this->item_id][$this->service_id];
			}
		} else {
			$a = array();
		}
*/
		return $this->getTable()->countComments($this->id, $sid, $where);

/*
		$a[$this->item_id][$this->service_id] = $res;
		Zend_Registry::set('countComments', $a);
 */
	}

	/**
	 * Чистка выдаваемого в браузер текста
	 */
	function escape()
	{
		if ($this->user_id) {
			return Site::escape($this->comment_text, false);
		} else {
			return Site::escape($this->comment_text);
		}
	}

}

class Zx_Db_Table_Rowset_Comment extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Comment extends Zx_Db_Table
{
	protected $_prefix = 'comment';

    protected $_name = 'comments';
    protected $_rowClass = 'Zx_Db_Table_Row_Comment';
    protected $_rowsetClass = 'Zx_Db_Table_Rowset_Comment';
    protected $_referenceMap = array
	(
		'content' => array(
	        'columns'		=> array('pid'),
	        'refTableClass'	=> 'content',
	        'refColumns'	=> array('id')
    	),
		'c_positions' => array(
	        'columns'		=> array('pid'),
	        'refTableClass'	=> 'c_positions',
	        'refColumns'	=> array('id')
    	),
		'blacklist' => array(
	        'columns'		=> array('comment_id'),
	        'refTableClass'	=> 'Zx_Db_Table_Comment',
	        'refColumns'	=> array('comment_id')
    	)
	);

	// соотношение префиксов и Id
	protected $_ids = array(
		1 => 'content',
		2 => 'content2',
		3 => 'c_positions',
	);

	function getServiceName($id)
	{
		switch ($id) {
			case 1:	return 'Информация';
			case 2:	return 'Статьи';
			case 3:	return 'Каталог';
			default: return '?';
		};
	}

	function getServiceIdByName($name)
	{
		return array_search($name, $this->_ids);
	}

	function getServiceById($id)
	{
		if (!empty ($this->_ids[$id])) {
			return $this->_ids[$id];
		} else {
			return false;
		}
	}

	/**
	 * Проверка на флуд!
	 * @param array $data
	 * @return boolean
	 */
	function isFlood($data)
	{
		$select = $this->select()
			->from('comments', 'COUNT(*) AS cnt')
			->where('item_id=?', $data['item_id'])
			->where('service_id=?', $data['service_id'])
			#->where('user_name=?', $data['user_name'])
			#->where('comment_visible=1')
			->where('comment_date > DATE_SUB('.PHPNOW.', INTERVAL 1 DAY)')
			->where('comment_text=?', $data['comment_text']);

		$row = $this->fetchRow($select);

		if ($row->cnt)
		{
			l('user: ' . $data['user_name'] . ', text: len=' . strlen($data['comment_text']) . ', md5=' . md5($data['comment_text']) , 'USER_COMMENT_FLOOD');
		}

		return $row->cnt;
	}


	/**
	 *
	 * @param integer $pid parent_id
	 * @param mixed $sid service_id
	 * @param string $where
	 * @return <type>
	 */
	function countComments($pid, $sid, $where = null)
	{
		if (is_string($sid)) {
			$sid = $this->getServiceIdByName($sid);
		}
		if (!$sid) {return 0;}

		$select = $this->select()
			->from($this->_name, 'COUNT(*) AS cnt')
			->where('pid=?', $pid)
			->where('sid=?', $sid)
			->where('flag_status=1');

		// additional where
		if (is_array($where))
		{
			foreach ($where as $k => $v)
			{
				$select = $select->where($k . '=?', $v);
			}
		}

		$row = $this->fetchRow($select);
		return $row->cnt;
	}

	function getComments($prefix, $item_id, $full = true)
	{
		$service_id = $this->getServiceIdByName($prefix);

		if ($full) {
			$select = $this->select()
				->where('item_id=?', $item_id)
				->where('service_id=?', $service_id)
				->where('comment_visible=1')
				->order('comment_date DESC');
			$comments = new Zend_Paginator(new Zend_Paginator_Adapter_DbTableSelect($select));
			$comments->setItemCountPerPage(20);
			$comments->setCurrentPageNumber(Zend_Registry::get('page'));
			#$comments->setCurrentPageNumber($this->page);
		} else {
			$comments = $this->fetchAll(
				$this->select()
				->where('item_id=?', $item_id)
				->where('service_id=?', $service_id)
				->where('comment_visible=1')
				->order('comment_date DESC')
				->limit(3)
			);
		}
		return $comments;
	}

	/**
	 * @deprecated Куки ставим через JS!
	 */
	static function setCookie()
	{
		if (empty($_COOKIE['comments']))
		{
			$value = md5(time() . $_SERVER['REMOTE_ADDR'] . 'zf');
			$res = setcookie('comments', $value, time() + 86400, '/', $_SERVER['HTTP_HOST'], false, true);
			if (!$res) {l($res, 'USER_COOKIE_FAIL');}
			#$cookie = new Zend_Http_Cookie('aif.comments', $value, $_SERVER['HTTP_HOST'], time() + 604800);
		}
	}

	/**
	 *
	 */
	function blacklistByCookies($aIds)
	{
		$aIds = array_unique($aIds);

		$blacklist = new Zx_Db_Table_CommentBlacklist();
		$users = new Users();

		foreach ($aIds as $user_cookie)
		{
			if (empty($user_cookie)) {continue;} // на всякий

			if (strlen($user_cookie) == 32)
			{
				$data = array(
					'user_cookie' => $user_cookie,
					'adm_id' => '', //todo
					'comment_id' => '' //todo
				);
				$blacklist->insertData($data);

				#$select = $this->select()->where('user_cookie =?', $user_cookie);
				$where = $this->getAdapter()->quoteInto('user_cookie = ?', $user_cookie);
				$this->delete($where);

			// зарегеный
			} elseif (is_numeric($user_cookie))
			{
				// todo: логирование!
				$res = $users->ban($user_cookie);
			}
		}
	}

	function createComment($data)
	{
		$res = $this->isFlood($data);
		if ($res) {return false;}

		l($data, 'INSERT_USER_COMMENT');
		$res = $this->insert($data);
		l('INSERT_USER_COMMENT res: ' . $res);
		return $res;
	}

	static function setPremoderation($b = true)
	{
		Zend_Registry::set('premoderation', $b);
	}

	static function isPremoderation()
	{
		if (!Zend_Registry::isRegistered('premoderation'))
		{
			Zend_Registry::set('premoderation', true);
		}
		return Zend_Registry::get('premoderation');
	}

}