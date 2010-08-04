<?php
/**
* Advanced array_merge_recursive()
* http://ru2.php.net/manual/en/function.array-merge-recursive.php
* @param array $arr
* @param array $ins
* @return
* @static
*/
function my_array_merge_recursive($arr,$ins)
{
	if (is_array($arr)) {
		if (is_array($ins)) {
			foreach($ins as $k=>$v) {
				if (isset($arr[$k])&&is_array($v)&&is_array($arr[$k])) {
					$arr[$k] = my_array_merge_recursive($arr[$k],$v);
				} else {
					$arr[$k] = $v;
				}
			}
		}
	} elseif(!is_array($arr)&&(strlen($arr)==0||$arr==0)){
		$arr=$ins;
	}
	return($arr);
}

/**
* UTF-8 mighty strtoupper
* @param string $s
* @return string
*/
function strUp($s)
{
	return mb_strtoupper($s, 'UTF-8');
}

/**
* UTF-8 mighty strtolower
* @param string $s
* @return string
* @todo
*/
function strLo($s)
{
	return mb_strtolower($s, 'UTF-8');
}

/**
* UTF-8 mighty ucfirst
* @param string $s
* @return string
* @todo
*/
function strUpFirst($s)
{
	return strUp(mb_substr($s, 0, 1)) . strLo(mb_substr($s, 1));
}


/**
* format price for current locale (if exists)
* @param
* @return
*/
function nlsPrice($price, $precision = true) {

$price = str_replace(',', '.', $price);// запятые в точки (русские десятичные)
if ($precision) {
	return number_format($price, 2, '-', '.');
} else {
	return number_format($price, 0, '-', '.');
}
}

/**
 * We love UTF-8!
 * @param string_with_bad_ugly_codepage $str
 * @return kiss_kiss_bang_bang
 */
function u($str) {
	return iconv( 'cp1251', 'utf-8', $str );
}

/**
 * Получить первый день текущей недели
 * @param int $week
 */
function getFirstDayOfWeek($week = null)
{
	#$timestamp = mktime(0, 0, 0, date('m'), date('d') - date('w'), date('Y'));
	$timestamp = strtotime(date('Y')."W".date('W')."1");
	return $timestamp;
}

/**
 * TS 2 MySQL
 * @param int $ts
 * @return string
 */
function dateMySQL($ts = null, $tm = true)
{
	if (is_null($ts)) {
		$ts = time();
	}

	if ($tm) {
		return date('Y-m-d H:i:s', $ts);
	} else {
		return date('Y-m-d', $ts);
	}
}

/**
 * Check if date
 * http://www.php.net/manual/en/function.checkdate.php#90345
 * @param <type> $str
 * @return <type>
 */
function is_date( $str )
{
  $stamp = strtotime( $str );

  if ($stamp === false)
  {
     return FALSE;
  }
  $month = date( 'm', $stamp );
  $day   = date( 'd', $stamp );
  $year  = date( 'Y', $stamp );

  if (checkdate($month, $day, $year))
  {
     return TRUE;
  }

  return FALSE;
}


/**
 * Контрольный дамп безопасности
 * @param string $fn
 */
function dumpLog($fn, $full = true)
{
	$alert = false;
	$dump = '';

	//-- security raw dump (since 15.4.2010)
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$fn = str_replace('.log', '.dump.log', $fn);
		if (!empty($_POST))
		{
			$post = $_POST;
			if (!empty($post['password'])){$post['password'] = '***';}
			$dump .= "\n_POST: " . print_r($post, 1);

			foreach ($post as $k => $v)
			{
				if (shitSniffer($v)) {$alert = "_POST[" . $k . "]"; break;}
			}
		}
		if (!empty($_GET)) {
			$dump .= "\n_GET: " . print_r($_GET, 1);

			if (!$alert)
			{
				foreach ($_GET as $k => $v)
				{
					if (shitSniffer($v)) {$alert = "_GET[" . $k . "]"; break;}
				}
			}
		}
		#if (!empty($_COOKIE)) {$dump .= "\n_COOKIE: " . print_r($_COOKIE, 1);}
		#if (!empty($_SESSION)) {$dump .= "\n_SESSION: " . print_r($_SESSION, 1);}
		#if (!empty($_ENV)) {$dump .= "\n_ENV: " . print_r($_ENV, 1);}
		if (!empty($_SERVER))
		{
			$server = array();
			#SERVER_NAME
			if (!empty($_SERVER['REMOTE_ADDR'])) {$server['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];}
			if (!empty($_SERVER['HTTP_USER_AGENT'])) {$server['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];}
			if (!empty($_SERVER['HTTP_HOST'])) {$server['HTTP_HOST'] = $_SERVER['HTTP_HOST'];}
			#if (!empty($_SERVER['HTTP_ORIGIN'])) {$server['HTTP_ORIGIN'] = $_SERVER['HTTP_ORIGIN'];}
			if (!empty($_SERVER['REQUEST_URI'])) {
				$server['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
				if (!$alert && shitSniffer($server['REQUEST_URI'])) {$alert = '_SERVER[REQUEST_URI]';}
			}
			if (!empty($_SERVER['QUERY_STRING'])) {
				$server['QUERY_STRING'] = $_SERVER['QUERY_STRING'];
				if (!$alert && shitSniffer($server['QUERY_STRING'])) {$alert = '_SERVER[QUERY_STRING]';}
			}
			if (!empty($_SERVER['HTTP_REFERER'])) {$server['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];}
			#if (!empty($_SERVER['SCRIPT_NAME'])) {$server['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];}
			#if (!empty($_SERVER['SCRIPT_FILENAME'])) {$server['SCRIPT_FILENAME'] = $_SERVER['SCRIPT_FILENAME'];}
			if (!empty($_SERVER['HTTP_CONTENT_TYPE'])) {$server['HTTP_CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];}
			#unset($_SERVER['LS_COLORS']);
			#unset($_SERVER['HTTP_COOKIE']); // это есть в _COOKIE
			$dump .= "\n_SERVER: " . print_r($server, 1);
		}

		$dump = "\n[" . date('c') . "] " . ($alert ? '[ALERT!] ' : '') . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI'] . $dump . ($alert ? "\nALERT: " . $alert : '');
		error_log($dump, 3, $fn);
	}

	// todo: внедрить нормальные снифферы логов сервера и убрать отсюда!
	elseif (!empty($_GET))
	{
		foreach ($_GET as $k => $v)
		{
			if (shitSniffer($v))
			{
				$alert = "_GET[" . $k . "]";
				$dump = print_r($_GET, 1);
				break;
			}
		}
		if ($alert)
		{
			$dump = "\n[" . date('c') . "] " . ($alert ? '[ALERT!] ' : '') . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI'] . $dump . ($alert ? "\nALERT: " . $alert : '');
			$fn = str_replace('.log', '.dump.log', $fn);
			error_log($dump, 3, $fn);
		}
	}

	if ($alert && $full)
	{
		if (defined('local.edition'))
		{
			echo 'DEBUG:<br><textarea rows=10 cols=100>' . print_r($dump, 1) . '</textarea><br>';
		} else {
			mail('coder4web@gmail.com', 'POST alert!', $dump);
		}
		die; // yeah, die, yo shit!
	}

}

/**
 * Portable shit sniffer :)
 * @param string $s
 * @return boolean
 */
function shitSniffer($s)
{
	if (empty($s)) {return false;}
	if (is_array($s)) {$s = print_r($s, 1);}
	$a = array('select', 'insert', 'update', 'delete', 'drop', 'grant');#, 'from'
	#$s = strip_tags($s);
	$s = stripslashes($s);
	$s = strtolower($s);
	foreach ($a as $v)
	{
		if (strpos($s, $v) !== false)
		{
			return true;
		}
	}
	return false;
}


/**
* Firebug (or any other writer) debug
* @param mixed $value
* @param string $label
* @return void
*/
function l($value, $label = null, $level = Zend_Log::INFO, $logger = null)
{
	if (!ZX_LOG) {return false;}
	if ($value === '') {return false;}
	$log = '';
	$suffix = '';

	if (!is_null($label)) {
		if ($label[0] == 'U' && $label[1] == 'S')// user data
		{
			$suffix = 'HTTP_USER_AGENT=' . $_SERVER['HTTP_USER_AGENT'] . "\n" .
				'REMOTE_ADDR=' . $_SERVER['REMOTE_ADDR'] . "\n" .
				(!empty($_SERVER['HTTP_REFERER']) ? 'HTTP_REFERER=' . $_SERVER['HTTP_REFERER'] . "\n" : '') .
				(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? 'HTTP_X_FORWARDED_FOR=' . $_SERVER['HTTP_X_FORWARDED_FOR'] . "\n" : '') .
				'REQUEST_URI=' . $_SERVER['REQUEST_URI'];
		}
        $log = '[' . $label . '] ';
	}

	if ( is_array($value) ) {
		$log .= print_r($value, 1);
	} elseif (is_object($value)) {
		if ($value instanceof Zend_Db_Table_Select) {
			$log .= $value->__toString();
		} else {
			$log .= print_r($value, 1);
		}
	} else {
        $log .= $value;
    }

	// message
	$log = $log . ', UID=' . UID . ' (' . (microtime(1) - T0) . ' s.)';

	if (!empty($suffix)) {
		$log .= "\n" . $suffix;
	}

	if (is_null($logger)) {$logger = Zend_Registry::get('logger');}
	$logger->log($log, $level);
}


/**
* Firebug (or any other writer) debug
* @param string $message
* @param string $label
* @return void
*/
/*
function l($message, $label = null, $level = Zend_Log::INFO)
{
	if (!ZX_LOG) {return false;}
	$log = '';

	if (!is_null($label)) {
        $log = '[' . $label . '] ';
    }

	if (is_array($message)) {
        $log .= print_r($message, 1);
	} else {
        $log .= $message;
    }

	$logger = Zend_Registry::get('logger');
	$logger->log($log, $level);
}
*/

/**
 * Pure zen debug
 * @param mixed $v
 * @param boolena $exit
 */
function d($v, $exit = true)
{
	if ($v instanceof Zend_Db_Table_Select) {
		$dump = $v->__toString();
	} else{
		$dump = print_r($v, 1);
	}
	echo 'DEBUG:<br><textarea rows=10 cols=100>' . $dump . '</textarea><br>';
	if ($exit) {exit;}
}