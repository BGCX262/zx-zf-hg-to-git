<?php
/**
* Poll Log model (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 6/16/2009
*/
class Zx_Db_Table_PollLogRow extends Zx_Db_Table_Row
{
}

class Zx_Db_Table_PollLogRowset extends Zx_Db_Table_Rowset
{
}

class Zx_Db_Table_PollLog extends Zx_Db_Table
{
	protected $_name = 'polls_log';
	protected $_primary = 'id';

	protected $_rowClass = 'Zx_Db_Table_PollLogRow';
	protected $_rowsetClass = 'Zx_Db_Table_PollLogRowset';

    protected $_referenceMap = array(
/* 		'All' => array(
	        'columns'           => array('poll_id', 'data_id'), // так не получится, так как 2 разные родительские таблицы!
	        'refTableClass'     => 'Zx_Db_Table_Poll',
	        'refColumns'        => array('id')
    	),
*/
		'Poll' => array(
	        'columns'           => array('poll_id'),
	        'refTableClass'     => 'Zx_Db_Table_Poll',
	        'refColumns'        => array('id')
    	),
		'PollData' => array(
	        'columns'           => array('data_id'),
	        'refTableClass'     => 'Zx_Db_Table_PollData',
	        'refColumns'        => array('id')
    	),
	);
}