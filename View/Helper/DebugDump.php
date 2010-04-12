<?php
/**
* DebugDump
*/
class Zx_View_Helper_DebugDump extends Zend_View_Helper_Abstract
{
	function debugDump()
	{
		if ( !defined('ZX_DEBUG') || (ZX_DEBUG < 2) ) {return false;}
		$content = '';
		$a = array();
		
		$conf = Zend_Registry::get('conf');
		if (empty($conf->debug->on)) {return $content;}
		
		$db = Zend_Registry::get('db');

		$profiler = $db->getProfiler();
		
		if (!is_object($profiler)) {return $content;}

		#$res = $profiler->getQueryProfiles();
		#$a['dumpQueryProfiles'] = "DEBUG:<br><textarea rows=10 cols=100>" . print_r($res, 1) . "</textarea><br>";

		// http://framework.zend.com/manual/en/zend.db.profiler.html
		$totalTime = $profiler->getTotalElapsedSecs();
		$queryCount = $profiler->getTotalNumQueries();
		$longestTime = 0;
		$longestQuery = null;
		
		$a['dumpQueries'] = array();
		
		$queries = $profiler->getQueryProfiles();
		
		if (!empty($queries))
		{
			foreach($profiler->getQueryProfiles() as $query)
			{
				$time = $query->getElapsedSecs();
				$q = $query->getQuery();
				
				if ($time > $longestTime) {
					$longestTime  = $time;
					$longestQuery = $q;
				}
				
				$a['dumpQueries'][] = array(
					0 => $q,
					'time' => $time,
				);
			}
		}
		
		
		$a['dumpQueriesTotal'][] = array(
			'totalNumQueries' => array( 
				$profiler->getTotalNumQueries(),
				'Total queries'
			),
			'totalElapsedSecs' => array( 
				$profiler->getTotalElapsedSecs(),
				'Total queries length'
			),    
			'longestQuery' => array( 
				$longestQuery,
				'Longest query'
			), 
			'longestTime' => array( 
				$longestTime,
				'Longest query length'
			), 
			'averageQueryLength' => array( 
				($queryCount ? ($totalTime / $queryCount) : 0),
				'Average query length'
			), 
			'queriesPerSecond' => array( 
				($totalTime ? ($queryCount / $totalTime) : 0),
				'Queries per second'
			), 
		);
		
		foreach ($a as $k => $v)
		{
			// section title
			$content .= "<div class=debugHeader>" . $k . ":</div>\n";
			
			$isNum = (count($v) > 1) ? true : false;
			
			foreach ($v as $kk => $vv) {
				
				// item number
				if ($isNum) {$content .= "<div id=num>#" . $kk . "</div>\n";}
				
				// item properties
				foreach ($vv as $kkk => $vvv) {
					
					$content .= "<div id=" . (is_numeric($kkk) ? 'common' : $kkk) . ">";
					
					// если есть описание, то делаем акроним
					if (is_array($vvv)) {
						$content .= is_numeric($kkk) ? '' : "<acronym title='" . $vvv[1] . "'>" . $kkk . "</acronym>: ";
						$content .= $vvv[0];
					} else {
						$content .= is_numeric($kkk) ? '' : "<u>" . $kkk . "</u>: ";
						$content .= $vvv;
					}
					
					$content .= "</div>\n";
					
				}
				
			}
		}
		
		echo "<b>Zend_Config</b>:<br><textarea rows=10 cols=100>" . print_r($conf->toArray(), 1) . "</textarea><br>";

		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		echo "<b>Zend_Controller_Request_Http</b>:<br><textarea rows=10 cols=100>" . print_r($request, 1) . "</textarea><br>";

		#echo "<b>Zend_View</b>:<br><textarea rows=10 cols=100>" . print_r($this->view, 1) . "</textarea><br>";

		#echo "<b>Zend_Controller_Front</b> debug dump:<br><textarea rows=10 cols=100>" . print_r($front, 1) . "</textarea><br>";
		
		return $this->view->partial('partials/debug_dump.phtml', array('content' => $content));
	}
}