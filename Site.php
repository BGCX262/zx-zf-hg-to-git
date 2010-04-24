<?php
class Zx_Site
{
	function __construct()
	{
		$this->conf = Zend_Registry::get('conf');
	}

	/**
	 * Чистка выдаваемого в браузер текста
	 * @package security
	 */
	static function escape($s, $full = true)
	{
		if ($full)
		{
			$s = htmlspecialchars($s);
			#$s = htmlentities($s);
		}

		return nl2br($s);
	}

	/**
	 * Zero fill
	 * @param integer $i
	 * @param array $conf
	 * @return string
	 */
	static function zerofill($i, $conf)
	{
		if (empty($conf['length'])) {$conf['length'] = 8;}

		if ( !empty($conf['hash']) )
		{
			switch ($conf['hash'])
			{
				case 'sha1': return sha1($i);
				default: return md5($i);
			}
		} else {
			return sprintf("%0" . $conf['length'] . "d", $i);
       	}
	}

}