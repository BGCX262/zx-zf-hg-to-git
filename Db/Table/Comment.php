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
	const EDIT_TIMELIMIT = 600; // in seconds

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
		'comments_blacklist' => array(
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
			->from($this->_name, 'COUNT(*) AS cnt')
			->where('pid=?', $data['pid'])
			->where('sid=?', $data['sid'])
			#->where('user_id=?', $data['user_id'])
			#->where('dt > DATE_SUB('.PHPNOW.', INTERVAL 1 DAY)')
			->where('txt=?', $data['txt']);

		$row = $this->fetchRow($select);

		if ($row->cnt)
		{
			l('user: ' . $data['user_id'] . ', text: len=' . strlen($data['txt']) . ', md5=' . md5($data['txt']) , 'USER_COMMENT_FLOOD');
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
		#d($sid);
		if (is_string($sid)) {
			$sid = $this->getServiceIdByName($sid);
		}
		if (!$sid) {return 0;}

		$select = $this->select()
			->from($this->_name, 'COUNT(*) AS cnt')
			->where('pid=?', $pid)
			->where('sid=?', $sid)
			->where('flag_status=1');
		#d($select);

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

	/**
	 * Get paginated comments for row
	 * @param <type> $sid
	 * @param <type> $item_id
	 * @param array $conf
	 * @return Zend_Paginator
	 */
	function getComments($sid, $item_id, $conf = array())
	{
		if (is_string($sid)) {
			$sid = $this->getServiceIdByName($sid);
		}

		$select = $this->select()
			->setIntegrityCheck(false)
			->from(array('p' => $this->_name))
			->join(array('u' => 'users'), 'u.id = p.user_id', array('name', 'surname', 'avatar'));

		if (!empty($conf['where'])) {
			$select = $select->where($conf['where']);
		} else {
			$select = $select->where('p.pid=?', $item_id)
				->where('p.sid=?', $sid);
		}
		
		$select = $select->where('p.flag_status=1')
			->where('u.flag_status=1');

		if (!isset($conf['asc']) || $conf['asc']) {
			$select = $select->order(array('p.dt', 'p.tm'));
		} else {
			$select = $select->order(array('p.dt DESC', 'p.tm DESC'));
		}

		if (!empty($conf['limit'])) {
			$select = $select->limit($conf['limit']);
		}
		#d($select);

		$comments = new Zend_Paginator(new Zend_Paginator_Adapter_DbTableSelect($select));
		$comments->setItemCountPerPage(10);
		$comments->setCurrentPageNumber(Zend_Registry::get('page'));
		#$comments->setCurrentPageNumber($this->page);

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

	function addComment($identityId)
	{
		$res = false;

		$form = new Form_Comment();
		$formData = Zend_Controller_Front::getInstance()->getRequest()->getPost();

		if ($form->isValid($formData))
		{
			$uid = $form->getValue('user_id', 0);
			if ($identityId != $uid) {
				l($uid, __METHOD__ . ': $uid invalid!', Zend_Log::DEBUG);
				return false;
			}

			$data = $form->getValues();
			$data['dt'] = $data['tm'] = dateMySQL();
			l($data, __METHOD__ . ': $data', Zend_Log::DEBUG);

			$res = $this->createComment($data);
		} else {
			l($formData, __METHOD__ . ': $formData invalid!', Zend_Log::DEBUG);
		}

		return $res;
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