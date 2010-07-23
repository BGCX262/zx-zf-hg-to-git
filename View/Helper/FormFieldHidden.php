<?php
class Zx_View_Helper_FormFieldHidden
{
    public function formFieldHidden($name, $value = '', $class = '', $id = '', $style = '')
    {
		$res = '<input type="hidden" name="' . $name . '" value="' . $value . '"';
		if (!empty($class)) {
			$res .= ' class="' . $class . '"';
		}
		if (!empty($id)) {
			$res .= ' id="' . $id . '"';
		}
		if (!empty($style)) {
			$res .= ' style="' . $style . '"';
		}
		$res .= ' />';
		return $res;
	}
}