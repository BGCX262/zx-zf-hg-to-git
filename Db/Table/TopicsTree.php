<?php
class Zx_Db_Table_TopicsTree extends Zx_Db_Table_Tree
{
	protected $_name = 'topics_tree';
	protected $_dependentTables = array('topics_data');
}