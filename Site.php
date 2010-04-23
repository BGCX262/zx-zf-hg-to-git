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

}