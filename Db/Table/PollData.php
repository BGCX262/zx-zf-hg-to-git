<?php
/**
* Poll data model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 6/16/2009
*/
class Zx_Db_Table_PollDataRow extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_PollDataRowset extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_PollData extends Zx_Db_Table
{
	protected $_name = 'polls_data';
	protected $_primary = 'id';
	
	protected $_rowClass = 'Zx_Db_Table_PollDataRow';
	protected $_rowsetClass = 'Zx_Db_Table_PollDataRowset';
	
	protected $_dependentTables = array('Zx_Db_Table_PollLog');	

    protected $_referenceMap = array(
		'Poll' => array(
	        'columns'           => array('poll_id'),
	        'refTableClass'     => 'Zx_Db_Table_Poll',
	        'refColumns'        => array('id')
    	),
	);
	
/*     public function __construct($config = array())
    {
		parent::__construct($config);
		
		if (!empty($config['poll_id']))
		{
			$select = $this->select()->where('flag_status = 1')->where('poll_id = ?', $config['poll_id'])->limit(1);
			$this->row = $this->fetchRow($select);
		}
	}
 */
}