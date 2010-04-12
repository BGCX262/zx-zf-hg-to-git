<?php
/**
* Based on Application_Db_Table_Nestedset (Петров Станислав)
* http://web-dev.info/2008/03/zf-nestedset/
* @todo
*/
class Zx_Db_Table_Nestedset extends Zend_Db_Table{

    const ERROR_NODE_NOT_EXIST = 19820;

    protected $_id = 'id';
    protected $_left = 'left';
    protected $_right = 'right';
    protected $_level = 'level';

    /**
     * Constructor
     *
     * @param array config
     */
    public function __construct($config = array()) {
        parent::__construct($config);
        $db = $this->getAdapter();
        $this->_left = $db->quoteIdentifier($this->_left);
        $this->_right = $db->quoteIdentifier($this->_right);
        $this->_level = $db->quoteIdentifier($this->_level);
    }

    /**
     * Unquote identifiers
     *
     * @param string quoted identifier
     * @return string unquoted identifier
     */
    private function _unquoteIdentifier($identifier) {
        return preg_replace('/\W/i', '', $identifier);
    }

    /**
     * Clear table and prepare it to work by insert root node (id = 1, left = 1, right = 2, level = 0)
     *
     * @param array root properties
     * @return integer table primary key value
     * @throws Zend_Db_Table_Exception
     */
    public function clear($properties = array()){
        //truncate
        $this->getAdapter()->query('TRUNCATE TABLE ' . $this->_name);
        //create root
        $root = $this->createRow($properties + array(
            $this->_unquoteIdentifier($this->_id) => 1,
            $this->_unquoteIdentifier($this->_left) => 1,
            $this->_unquoteIdentifier($this->_right) => 2,
            $this->_unquoteIdentifier($this->_level) => 0
        ));
        return $root->save();
    }

    /**
     * Returns a Left and Right IDs and Level of an node or null if node not exists
     *
     * @param integer node id
     * @return Zend_Db_Row_Abstract
     * @throws Zend_Db_Table_Exception
     */
    public function getNodeInfo($id){
        if (!$nodeInfo = $this->fetchRow($this->_id . ' = ' . (int)$id))
            throw new Zend_Db_Table_Exception('Can\'t fetch node row (id #' . $id . ')', self::ERROR_NODE_NOT_EXIST);
        else
            return $nodeInfo;
    }

    /**
     * Add new node
     *
     * @param integer parent node id
     * @param array node properties
     * @return table primary key value
     * @throws Zend_Db_Table_Exception
     */
    public function insertNode($id, $properties = array()){
        $parent = $this->getNodeInfo($id);

        //prepare other nodes
        $this->getAdapter()->query('UPDATE ' . $this->_name . ' SET ' .
            $this->_left . ' = IF (' . $this->_left . ' > ' . $parent->right . ', ' . $this->_left . ' + 2, ' . $this->_left . '), ' .
            $this->_right . ' = IF (' . $this->_right . ' >= ' . $parent->right . ', ' . $this->_right . ' + 2, ' . $this->_right . ') ' .
            'WHERE ' . $this->_right . ' >= ' . $parent->right);

        //new node
        $newRow = $this->createRow($properties + array(
            $this->_unquoteIdentifier($this->_left) => $parent->right,
            $this->_unquoteIdentifier($this->_right) => $parent->right + 1,
            $this->_unquoteIdentifier($this->_level) => $parent->level + 1));
        return $newRow->save();
    }

    /**
     * Add new node after some another
     *
     * @param integer node id
     * @param array node properties
     * @return table primary key value
     * @throws Zend_Db_Table_Exception
     */
    public function insertNodeAfter($afterId, $properties = array()){
        $node = $this->getNodeInfo($afterId);

        //prepare other nodes
        $this->getAdapter()->query('UPDATE ' . $this->_name . ' SET ' .
            $this->_left . ' = IF (' . $this->_left . ' > ' . $node->right . ', ' . $this->_left . ' + 2, ' . $this->_left . '), ' .
            $this->_right . ' = IF (' . $this->_right . ' > ' . $node->right . ', ' . $this->_right . ' + 2, ' . $this->_right . ') ' .
            'WHERE ' . $this->_right . ' >= ' . $node->right);

        //new node
        $newRow = $this->createRow($properties + array(
            $this->_unquoteIdentifier($this->_left) => $node->right +1,
            $this->_unquoteIdentifier($this->_right) => $node->right + 2,
            $this->_unquoteIdentifier($this->_level) => $node->level));
        return $newRow->save();
    }

    /**
     * Delete node
     *
     * @param integer node id
     * @param boolean delete with childs
     * @throws Zend_Db_Table_Exception
     */
    public function deleteNode($id, $withChilds = true){
        $node = $this->getNodeInfo($id);
        if ($withChilds){
            $this->_deleteNodeWithChilds($id, $node);
        } else {
            $this->_deleteNodeWithoutChilds($id, $node);
        }
    }

    /**
     * Delete one node, without childs
     *
     * @param integer node id
     * @throws Zend_Db_Table_Exception
     */
    private function _deleteNodeWithoutChilds($id, $node){
        //delete node
        if ($this->delete($this->_id . ' = ' . (int)$id)){
            //update other nodes
            $this->getAdapter()->query('UPDATE ' . $this->_name . ' SET ' .
                $this->_left . ' = IF (' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_left . ' -1, ' . $this->_left . '), '.
                $this->_right . ' = IF (' . $this->_right . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_right . ' -1, ' . $this->_right . '), ' .
                $this->_level . ' = IF (' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_level . ' -1, ' . $this->_level . '), ' .
                $this->_left . ' = IF (' . $this->_left . ' > ' . $node->right . ', ' . $this->_left . ' -2, ' . $this->_left . '), ' .
                $this->_right . ' = IF (' . $this->_right . ' > ' . $node->right . ', ' . $this->_right . ' -2, ' . $this->_right . ') ' .
                'WHERE ' . $this->_right . '>' . $node->left
            );
        } else {
            throw new Zend_Db_Table_Exception('Can\'t delete node row (id #' . $id . ')');
        }
    }

    /**
     *  Delete node, with childs
     *
     * @param integer node id
     * @throws Zend_Db_Table_Exception
     */
    private function _deleteNodeWithChilds($id, $node){
        //delete nodes
        if ($this->delete($this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right)){
            //update other nodes
            $deltaId = ($node->right - $node->left) + 1;
            $this->getAdapter()->query('UPDATE ' . $this->_name . ' SET ' .
                $this->_left . ' = IF(' . $this->_left . ' > ' . $node->left . ' , ' . $this->_left . ' - ' . $deltaId . ', '.$this->_left . '), ' .
                $this->_right . ' = IF(' . $this->_right . ' > ' . $node->left . ' , ' . $this->_right . ' - ' . $deltaId . ', '.$this->_right . ') ' .
                'WHERE ' . $this->_right . ' > ' . $node->right
            );
        } else {
            throw new Zend_Db_Table_Exception('Can\'t delete node row (id #' . $id . ')');
        }
    }

    /**
     * Return node childs
     *
     * If levelEnd isn't given, only children of levelStart levels are enumerated.
     * Level values should always be greater than zero.
     * Level 1 means direct children of the node
     *
     * @param integer node id
     * @param string|array $order order field name(s)
     * @param integer childs start level relative level from which start to enumerate children
     * @param integer childs end level the last relative level at which enumerate children
     * @return array
     * @throws Exception, Zend_Db_Table_Exception
     */
    public function getChildren($id, $order = null, $levelStart = 1, $levelEnd = 1){
        if ($levelStart < 0) throw new Exception('levelStart value can\'t be less zero');

        $where1 = ' AND ' . $this->_name . '.' . $this->_level;
        $where2 = '_' . $this->_name . '.' . $this->_level . ' + ';

        if(!$levelEnd) $whereSql = $where1 . ' >= ' . $where2 . (int)$levelStart;
        else {
            $whereSql = ($levelEnd <= $levelStart)
                ? $where1 . '=' . $where2 . (int)$levelStart
                : ' AND ' . $this->_name . '.' . $this->_level . ' BETWEEN _' . $this->_name . '.' . $this->_level . '+' . (int)$levelStart
                . ' AND _' . $this->_name . '.' . $this->_level . ' + ' . (int)$levelEnd;
        }

        $orderSql = array();
        if (!is_array($order)) $order = array($order);
        foreach ($order as $val)
            $orderSql[] = $this->_name . '.' . $this->getAdapter()->quoteIdentifier($val);

        return $this->getAdapter()->query('SELECT * FROM ' .
            $this->_name . ' _' . $this->_name . ', ' . $this->_name . ' ' .
            'WHERE _' . $this->_name . '.' . $this->_id . ' = ' . (int)$id . ' AND ' .
            $this->_name . '.' . $this->_left . ' BETWEEN _' . $this->_name . '.' . $this->_left . ' AND _' . $this->_name . '.' . $this->_right .
            $whereSql .
            ' ORDER BY ' . $this->_name . '.' . $this->_level . (!empty($orderSql) ? ', ' . implode(', ', $orderSql) : '')
        )->fetchAll();
    }

    /**
     * Return "leveled" rowset array of rows with some level
     *
     * @param integer level
     * @return array rows
     */
    public function getByLevel($level){
        return $this->fetchAll($this->_level . ' = ' . (int)$level)->toArray();
    }

    /**
     * Return array of nodes from node to it's top level parent
     *
     * @param integer node id
     * @param boolean include root node
     * @return array nodes data
     */
    public function getPath($id, $withRoot = false){
        return $this->getAdapter()->query('SELECT * FROM ' .
            $this->_name . ' _' . $this->_name . ', ' . $this->_name . ' ' .
            'WHERE _' . $this->_name . '.' . $this->_id . ' = ' . (int)$id . ' AND ' .
            '_' . $this->_name . '.' . $this->_left . ' BETWEEN ' . $this->_name . '.' . $this->_left . ' AND ' . $this->_name . '.' . $this->_right .
            ($withRoot ? '' : ' AND ' . $this->_name . '.' . $this->_left . ' > 1') .
            ' ORDER BY ' . $this->_name . '.' . $this->_left
        )->fetchAll();
    }

    /**
     * Return parent row
     *
     * @param integer node id
     * @param integer relative level of parent
     * @return parent row as assoc array
     * @throws Exception
     */
    function getParent($id, $level = 1) {
        if($level < 1) throw new Exception('level can\'t be less by one');

        return $this->getAdapter()->fetchRow('SELECT * FROM ' .
            $this->_name . ' _' . $this->_name . ', ' . $this->_name . ' ' .
            'WHERE _' . $this->_name . '.' . $this->_id . ' = ' . (int)$id . ' AND ' .
            '_' . $this->_name . '.' . $this->_left . ' BETWEEN ' . $this->_name . '.' . $this->_left . ' AND ' . $this->_name . '.' . $this->_right . ' AND ' .
            $this->_name . '.' . $this->_level . ' = _' . $this->_name . '.' . $this->_level . ' - ' . $level
        );
    }

    /**
     * Move node with all it's childs to another node
     *
     * @param integer id of moving node
     * @param integer id of new parent node
     * @return boolean operation status
     */
    public function moveNode($id, $newParentId) {
        $node = $this->getNodeInfo($id);
        $newParent = $this->getNodeInfo($newParentId);
        //nothing to move
        if ($id == $newParentId || $node->left == $newParent->left) return true;
        //it is imposible to move a high-level node in a low-level
        if ($newParent->left >= $node->left && $newParent->left <= $node->right) return false;

        if ($newParent->left < $node->left && $newParent->right > $node->right && $levelP < $level - 1 ) {
            $sql = 'UPDATE ' . $this->_name . ' SET ' .
                $this->_level . ' = IF(' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_level . sprintf('%+d', -($node->level - 1) + $newParent->level) . ', ' . $this->_level . '), ' .
                $this->_right . ' = IF(' . $this->_right . ' BETWEEN ' . ($node->right + 1) . ' AND ' . ($newParent->right - 1) . ', ' . $this->_right . ' - ' . ($node->right - $node->left + 1) . ', ' .
                'IF(' . $this->_left . ' BETWEEN ' . ($node->left) . ' AND ' . ($node->right) . ', ' . $this->_right . ' + ' . ((($newParent->right - $node->right - $node->level + $newParent->level) / 2) * 2  +  $node->level - $newParent->level - 1) . ', ' . $this->_right . ')),  ' .
                $this->_left . ' = IF(' . $this->_left . ' BETWEEN ' . ($node->right + 1) . ' AND ' . ($newParent->right - 1) . ', ' . $this->_left . ' - ' . ($node->right - $node->left + 1) . ', ' .
                'IF(' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . ($node->right) . ', ' . $this->_left . ' + ' . ((($newParent->right - $node->right - $node->level + $newParent->level) / 2) * 2  +  $node->level - $newParent->level - 1) . ', ' . $this->_left .  ')) ' .
                'WHERE ' . $this->_left . ' BETWEEN ' . ($newParent->left + 1) . ' AND ' . ($newParent->right - 1);
        } elseif($newParent->left < $node->left) {
            $sql  =  'UPDATE ' . $this->_name . ' SET ' .
                $this->_level . ' = IF(' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_level . sprintf('%+d', -($node->level - 1) + $newParent->level) . ', ' . $this->_level . '), ' .
                $this->_left . ' = IF(' . $this->_left . ' BETWEEN ' . $newParent->right . ' AND ' . ($node->left - 1) . ', ' . $this->_left . ' + ' . ($node->right - $node->left + 1) . ', ' .
                'IF(' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_left . ' - ' . ($node->left - $newParent->right) . ', ' . $this->_left . ') ' .
                '), ' .
                $this->_right . ' = IF(' . $this->_right . ' BETWEEN ' . $newParent->right . ' AND ' . $node->left . ', ' . $this->_right . ' + ' . ($node->right - $node->left + 1) . ', ' .
                   'IF(' . $this->_right . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_right . ' - ' . ($node->left - $newParent->right) . ', ' . $this->_right . ') ' .
                ') ' .
                'WHERE ' . $this->_left . ' BETWEEN ' . $newParent->left . ' AND ' . $node->right .
                ' OR ' . $this->_right . ' BETWEEN ' . $newParent->left . ' AND ' . $node->right;
        } else {
            $sql  =  'UPDATE ' . $this->_name . ' SET ' .
                $this->_level . ' = IF(' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_level . sprintf('%+d', -($node->level - 1) + $newParent->level) . ', ' . $this->_level . '), ' .
                $this->_left . ' = IF(' . $this->_left . ' BETWEEN ' . $node->right . ' AND ' . $newParent->right . ', ' . $this->_left . ' - ' . ($node->right - $node->left + 1) . ', ' .
                   'IF(' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_left . ' + ' . ($newParent->right - 1 - $node->right) . ', ' . $this->_left . ')' .
                '), ' .
                $this->_right . ' = IF(' . $this->_right . ' BETWEEN ' . ($node->right + 1) . ' AND ' . ($newParent->right - 1) . ', ' . $this->_right . '-' . ($node->right - $node->left + 1) . ', ' .
                   'IF(' . $this->_right . ' BETWEEN ' . $node->left . ' AND ' . $node->right . ', ' . $this->_right . ' + ' . ($newParent->right - 1 - $node->right) . ', ' . $this->_right . ') ' .
                ') ' .
                'WHERE ' . $this->_left . ' BETWEEN ' . $node->left . ' AND ' . $newParent->right .
                ' OR ' . $this->_right . ' BETWEEN ' . $node->left . ' AND ' . $newParent->right;
        }
        return $this->getAdapter()->query($sql);
    }

    /**
     * Return siblings of node included same node
     *
     * @param node id
     * @return array
     * @throws Exception, Zend_Db_Table_Exception
     */
    public function getSiblings($id) {
        $result = array();
        if ($parent = $this->getParent($id))
            return $this->getChildren($parent[$this->_id]);
        return $result;
    }
} 