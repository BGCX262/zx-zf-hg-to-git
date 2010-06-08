<?php
/**
* Модель записи (row) БД
* @author Дерягин Алексей (aleksey@deryagin.ru)
* @version 8/25/2009
*/
class Zx_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{
	function getDate($field = 'dt')
	{
		if (!empty($this->dtf)) {
			return $this->dtf;
		} else {
			return date('d.m.Y', strtotime($this->$field)); // temp!
/* 			$locale = Zend_Registry::get('Zend_Locale');
			#$date = new Zend_Date(substr($this->dt, 0, 10), 'dd.MM.YYYY', $locale);
			$date = new Zend_Date(substr($this->dt, 0, 10), null, $locale);
			return date('d.m.Y', $date->getTimestamp());
			#return $date->get('dd.mm.YYYY'); // bugged :(
 */		}
	}

	function getDateTime()
	{
		if (!empty($this->dtf)) {
			return $this->dtf;
		} else {
			$locale = Zend_Registry::get('Zend_Locale');
			return new Zend_Date($this->dt, 'dd.MM.YYYY HH:mm:ss', $locale);
		}
	}


	/**
	* Get full/preview image
	* @param mixed $full boolean or string image type (true for full image, false for preview if boolean)
	* @param boolean $fs
	* @return string
	*/
	function getImage($conf = null)
	{
		return $this->getTable()->getImage($this->id, $conf);
	}

	/**
	* Get TN image
	* @param integer $tn_id
	* @param array $conf
	* @return string
	*/
	function getTN($tn_id, $conf = null)
	{
		return $this->getTable()->getImage(array($this->id, $tn_id), $conf);
	}

	/**
	* Get currency
	* @param string $field
	* @param boolean $full
	* @return string
	*/
	function getPrice($field, $decimals = 0)
	{
		return number_format($this->$field, $decimals, '-', '.');
	}

	/**
	* Get mask (fo/fn) for data files for row
	* @param boolean $fs
	* @return array|false
	*/
	function getFilesMask()
	{
		$name = $this->getTable()->info(Zend_Db_Table::NAME); #'name'
		$conf = $this->getTable()->files;

		// filename w/o extension
		$fn = sprintf("%0" . $conf['length'] . "d", $this->id);
		if ($conf['prefixes']) {$fn = $name . $fn;}

		// path
		$fo = $conf['folder'] . $name . "/";

		return array(
			'fo' => PATH_PUB . $fo,
			'fov' => '/' . $fo, // virtual			
			'fn' => $fn,
		);
	}

	/**
	* Get data files for row
	* @param
	* @return
	* @todo via DB (files, files_table), not only FS
	*/
	function getFiles($a = null)
	{
		if (!is_array($a)) {
			$a = $this->getFilesMask();
		}

		$res = array();
		$dir = opendir($a['fo']);
		while(($file = readdir($dir)) !== false) {
			if ( ($file != '.') && ($file != '..') && (strpos($file, $a['fn']) !== false ) )
			{
				#if (is_readable($file)) {
					$res[] = pathinfo($a['fov'] . $file);
				#}
			}
		}
		closedir($dir);
		return $res;
	}

	function updateHits()
	{
		return $this->getTable()->updateHits($this->id);
	}

	/**
	 * $this->txt wrapper
	 */
	function text()
	{
		return $this->txt;
    }

	protected function _upload()
	{
		#$upload = new Zend_File_Transfer_Adapter_Http();
		#$files = $upload->getFileInfo();
		#d($files);
		return false;
	}

	function upload()
	{
		#d($this);
		$res = $this->_upload();#d($res);

		if ($res) {
			$this->getTable()->setN(FrontEnd::getMsg(array('upload', 'ok')), 'success');
		} else {
			$this->getTable()->setN(FrontEnd::getMsg(array('upload', 'fail')), 'errors');
		}
		return $res;
	}

	function getSID()
	{
		$table = $this->getTable();#d($table);
		#d($table->_sid);

		if ($table->_sid > 0)
		{
			return $table->_sid;
		} else {
			return $table->info(Zend_Db_Table::NAME);
		}
	}


	/**
	 * Count comments for row
	 * @param array $where
	 * @return integer
	 */
	function countComments($where = null)
	{
		$comment = new Zx_Db_Table_Comment();
		return $comment->countComments($this->id, $this->getSID(), $where);
	}


	/**
	 * Get comments for row
	 * @param boolean $full
	 * @return
	 */
	function getComments($full = true)
	{
		$comment = new Zx_Db_Table_Comment();
		$comments = $comment->getComments($this->getSID(), $this->id, $full);
		#$comments->setItemCountPerPage(20);
		#$comments->setCurrentPageNumber(Zend_Registry::get('page'));
		return $comments;
	}

	function createComment($data)
	{
		// проверить общие установки
		$comment = new Zx_Db_Table_Comment();
		$data['service_id'] = $comment->getServiceIdByPrefix($this->getPrefix());
		$data['item_id'] = $this->getField('id');
		$data['info'] = My_Spambroker::userInfo();

		if (empty($data['comment_visible']))
		{
			$data['comment_visible'] = !$comment->isPremoderation();
		}

		$this->_rowdata = $data;

		$res = $comment->createComment($data);
		return $res;
	}

	/**
	 * Some post-update manipulations
	 */
	protected function _postUpdate()
	{
		return true;
	}

}

