<?php
/**
* @todo
* Partial wrapper
* @author Дерягин Алексей (aleksey@deryagin.ru)
* @version 6/17/2009
*/
class Zx_View_Helper_PartialExtended extends Zend_View_Helper_PartialLoop
{
	protected $arrayExtended = array();
	
	/**
	* @usage $items = $this->view->partialLoopExtended('partials/index_hot.phtml', $res, array('test' => 'test2'));
	* @usage $this->view->partialLoopExtended('partials/stores_list.phtml', $rows, array('controller' => $this->view->controller));
	* @param
	* @return
	*/
	public function partialExtended($path, $array, $arrayExtended = null)
	{
		
		if (!empty($arrayExtended) && is_array($arrayExtended)) {
			$this->arrayExtended = $arrayExtended;
		}
		
		return $this->partial($path, $array);
	}
	
	public function partial($name, $module, $model) {
		
		if (!is_array($model)) {
			$model = $model->toArray();
		}
		
		if (!empty($this->arrayExtended))
		{
			foreach ($this->arrayExtended as $k => $v) {
				if (!isset($model[$k])) { // foolproof!
					$model[$k] = $v;
				}
			}
		} else { // NB! Try to not be lazy bastard and set arrayExtended!
			$model['view'] = $this->view;
			#$model['config'] = $this->config;
		}
		
		return parent::partial($name, $module, $model);
	}
}