<?php
/**
* Debug plugin
* @todo
* @deprecated
*/
class Zx_Controller_Plugin_Debug extends Zend_Controller_Plugin_Abstract
{
	
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
		if (is_null($viewRenderer->view)) {$viewRenderer->init();}// ensure view is created by the view renderer
		$this->view = $viewRenderer->view;
    }
	
    /**
	* 
	* @param
	* @return
	*/
	public function dispatchLoopShutdown()
	{
		$conf = Zend_Registry::get('conf');
	
		if (!empty($conf->debug->on))
		{
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			#$db = Zend_Registry::get('db');

			$profiler = $db->getProfiler();
			
			$this->view->debugDump = array();
			
			if (is_object($profiler))
			{
				$res = $profiler->getQueryProfiles();
				$this->view->debugDump['dumpQueryProfiles'] = "DEBUG:<br><textarea rows=40 cols=100>" . print_r($res, 1) . "</textarea><br>";

				$s = '';
				foreach($profiler->getQueryProfiles() as $query) {
					$s .= $query -> getQuery() . "<br/>Time: " . $query -> getElapsedSecs();
				}
				$this->view->debugDump['dumpQueries'] = $s;
			}
		}
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view, 1) . "</textarea><br>";die;
    }
}