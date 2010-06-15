<?php
/**
* Content model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 5/21/2009
*/
class Zx_Db_Table_Row_Content extends Zx_Db_Table_Row
{
	/**
	* HAZARD AREA!
	* @example
	*/
	function getTopic()
	{
		$topicsRows = Zend_Registry::get('topicsRows');
		if (!empty($topicsRows[$this->topic_id])) {
			return $topicsRows[$this->topic_id];
		} else {
			return null;
		}
/* 		$topics = new Zx_Db_Table_TopicsTree(); // This is DB overkill :(
		$row = $topics->getById($this->topic_id);
		return $row;
 */
	}
}

class Zx_Db_Table_Rowset_Content extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_Content extends Zx_Db_Table
{
	protected $_name = 'content';
	protected $_primary = 'id';
	protected $_rowClass = 'Zx_Db_Table_Row_Content';
	protected $_rowsetClass = 'Zx_Db_Table_Rowset_Content';

	// @todo
    protected $_referenceMap = array(
/*     	'Topic' => array(
	        'columns'           => array('topic_id'),
	        'refTableClass'     => 'Zx_Db_Table_Topic',
	        'refColumns'        => array('id')
    	),
*/
/* 		'Topic' => array(
	        'columns'           => array('topic_id'),
	        'refTableClass'     => 'Zx_Db_Table_TopicsTree',
	        #'refColumns'        => array('id') // It is optional to specify this element. If you don't specify the refColumns, the column(s) reported as the primary key columns of the parent table are used by default.
    	),
 */    );

	protected $_sid = 1;

/**
* NLS parameters for SQL selects
* @var
*/
/* 	protected $aNLS = array(
	'name' => 'content_nls',
	'cols' => 'c.*',
);
*/
	function init()
	{
		parent::init();

		$this->_data = array_merge($this->_data, array(
			'topicId' => 0,
			'topicIdNot' => 0,//todo
			#'filesPrefix' => 'content',
			'isDates' => true, // use dates
			'uriController' => 'news',
			'uriItem' => 'item',
		));
	}

	function getTopicParam()
	{
		if (!empty($this->topicId)) {return $this->topicId;}
		$topicId = $this->get('topicId');
		if ( !is_null($topicId) && !empty($topicId) ) {
			return $this->get('topicId');
		}
		$path = Zend_Registry::get('path');
		if (!empty($path['topicId'])) {  // eg /topic/:topicId/:page
			$topicId = $path['topicId'];
		}
		return $this->topicId = $topicId;
	}

	function getTopicWhere($topicId = null)
	{
		$where = '';

		if (is_null($topicId)) {
			$topicId = $this->getTopicParam();
		}

		if (!is_null($topicId))
		{
			$where = 'topic_id';
			if (is_array($topicId)) {
				$where .= " IN ('" . implode("','", $topicId) . "')";
			} else {
				$where .= " = '" . $topicId . "'";
			}
		}
		return $where;
	}


	/**
	* getRow() wrapper
	* @return Zx_Db_Table_ContentRow
	*/
	function getItem($id)
	{
		$conf = $this->isDates ? $this->confSQL('dtf') : '';
		$where = "id = '" . $id . "'";
		$row = $this->getRow($where, $conf);
		if (!$row) {throw new Zend_Controller_Action_Exception('Content Item (' . $where . ') not found', 404);}
		return $row;
	}


	/**
	*
	* @param
	* @return
	*/
	function getItemUri()
	{
		$s = '/' . $this->uriController;
		if ($this->uriItem) {
			$s .= '/' . $this->uriItem;
		}
		return $s;
	}

	/**
	* @usage $rows = $this->Content->getTopicContent(3); (ZF_KO::MainController)
	* @param
	* @return Zend_Db_Table_Rowset
	*/
	function getTopicContent($topicId = null, $conf = array())
	{
		$_conf = $this->confSQL('dtf', array('fields' => array('id', 'title', 'announce')));
		if (!empty($conf)) {
			$conf = array_merge($conf, $_conf);
		} else {
			$conf = $_conf;
		}
		$where = $this->getTopicWhere($topicId);
		
		$select = $this->getSelect($this->getTopicWhere($topicId), $conf);
#		d($select);
		$rows = $this->paginator($select);
		return $rows;
	}

	/**
	* @usage $rows = $this->Content->getLastRows(3); (ZF_RPN::IndexController)
	* @param integer $limit
	* @param array $conf
	* @return Zend_Db_Table_Rowset
	* @since 4/28/2009
	*/
	function getLastRows($limit, $conf = array())
	{
		$this->setPrefixes(true);
		#if (!empty($conf['join'])) {$this->setPrefixes(true);}

		$auxconf = array('fields' => array('c.id', 'c.title', 'c.topic_id', 'c.dt', 'c.announce'));
		if (!empty($conf['fields'])) {
			$auxconf['fields'] = array_merge($auxconf['fields'], $conf['fields']); 
		}

		if (!empty($conf)) {
			$conf = array_merge( $conf, $this->confSQL('dtf', $auxconf) );
		} else {
			$conf = $this->confSQL('dtf', $auxconf);
		}
		if (empty($conf['order'])) {
			$conf['order'] = array('c.flag_hot DESC', 'c.dt DESC', 'c.tm DESC');
		}
		$conf['limit'] = $limit;
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($conf, 1) . "</textarea><br>";die;

		if ($limit == 0) {
			#$res = $this->getRow($conf);
			$select = $this->getSelect($conf);
			$res = $this->paginator($select);
		} elseif ($limit == 1) {
			$res = $this->getRow($conf);
		} else {
			$res = $this->getRows($conf);
		}
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($res, 1) . "</textarea><br>";die;

		$this->setPrefixes(false);

		return $res;
	}
}