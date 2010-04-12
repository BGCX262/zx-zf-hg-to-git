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
	* @param boolean $full
	* @param boolean $fs
	* @return string
	*/
	function getImage($full = true, $fs = false)
	{
		return $this->getTable()->getImage($this->id, $full, $fs);
	}

	/**
	* Get TN image
	* @param integer $tn_id
	* @param boolean $full
	* @param boolean $fs
	* @return string
	*/
	function getTN($tn_id, $full = true, $fs = false)
	{
		return $this->getTable()->getImage(array($this->id, $tn_id), $full, $fs);
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
}

