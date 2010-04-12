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
    #return strtoupper(strtr($s, 'абвгдезийклмнопстуфхцшщъыьэюя', 'АБВГДЕЗИЙКЛМНОПСТУФХЦШЩЪЫЬЭЮЯ'));
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
    #return strtolower(strtr($s, 'АБВГДЕЗИЙКЛМНОПСТУФХЦШЩЪЫЬЭЮЯ', 'абвгдезийклмнопстуфхцшщъыьэюя'));
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