<?php
/**
* Модель таблицы БД
* @author Дерягин Алексей (aleksey@deryagin.ru)
* @version 8/15/2009
*/
class Zx_Db_Table extends Zend_Db_Table_Abstract
{
	protected $_primary = 'id';
    protected $_rowClass = 'Zx_Db_Table_Row';
    protected $_rowsetClass = 'Zx_Db_Table_Rowset';

	protected $_alias = null; // alias instead _name for images etc

	protected $search = array(); // search conf

	/**
	* Флаг NLS
	* @var
	*/
	protected $NLS = false;

	/**
	* NLS ID
	* @var
	*/
	protected $NLSId = 1;

	/**
	* NLS config array
	* @var
	*/
	protected $aNLS = array();

	protected $debug = false;

	protected $_data = array(); // get / set array
	
	protected $imgs = array(
		'folder' => '',
		'folderFiles' => 0, // max files (files IDs exactly) per folder
		'folderSubfolders' => 0, // max subfolder per folder
		'folderFill' => 4, // zero fill symbols
		'length' => 8,
		'ext' => '.jpg',
		'prefixes' => false,
		'hash' => '',
	);

	// video files
	protected $video = array(
		'folder' => 'files/video',
		'folderFiles' => 1000, // max files (files IDs exactly) per folder
		'folderSubfolders' => 0, // max subfolder per folder
		'folderFill' => 4, // zero fill symbols
		'length' => 8,
		'ext' => '.flv',
		'prefixes' => false,
		'hash' => 'md5',
	);

	protected $files = array(
		'folder' => 'files/',
		'length' => 8,
		'prefixes' => true,
	);

	protected $_paginator = false;

	protected $cprefix = ''; // child table prefix
	protected $pprefix = ''; // parent table prefix

	protected $_stored = null; // registry-stored rows! (since 10/4/22)

	/**
	* service_id for comments, votes etc
	* @var integer
	*/
	protected $_sid = 0;


	function init()
	{
		parent::init();

		// NB! если свойство должно быть не в data, то прописывать его индивидуально!
		$this->_data = array(
			'ItemCountPerPage' => 10,
			'isPaginator' => true,
			'isReturnPaginator' => false,
			'generalWhere' => '',
			'conditionWhere' => false,
			'isStoredRows' => false,
			/* 'paginatorFile' => 'partials/paginator.phtml' */
		);

		if ($this->_alias === null) {
			$this->imgs['folder'] = $this->_name;
		} else {
			$this->imgs['folder'] = $this->_alias;
		}
	}

	/**
	* Установка префиксов для выборок (с JOIN или NLS)
	* @param boolean $state
	* @return void
	*/
	protected function setPrefixes($state)
	{
		if ($state) {
			$this->cprefix = 'c.'; // child table prefix
			$this->pprefix = 'p.'; // parent table prefix
		} else {
			$this->cprefix = $this->pprefix = '';
		}
	}

	public function setNLS ($v = true, $id = 0)
	{
		$this->NLS = $v;
		if ($this->NLS) {
			if (!empty($id)) {$this->NLSId = $id;}

			//--< default aNLS configuration!
			if (empty($this->aNLS['name'])) {
				$this->aNLS['name'] = $this->_name . '_nls';
			}
			if (empty($this->aNLS['cols'])) {
				$this->aNLS['cols'] = array('c.id AS cid', 'c.nls_id AS nid', 'c.title', 'c.txt');
			}
			//-->

		} else {
			unset($this->aNLS); // clear NLS array
		}
	}


	/**
	* Подготовка конфигурации для SQL выборки
	* @param string/array $conf
	* @return array
	*/
 	public function confSQL($conf, $auxconf = null)
	{
		if ($this->NLS) {
			if (!is_array($conf) || empty($conf['skipNLS'])) {
				return $this->confNLS($conf);
			}
		}

		// empty action - default configuration
		if (empty($conf)) {
			$conf = array(
				'from' => array(
					$this->_name,
					'*'
				)
			);

		// custom configuration
		} elseif (!is_array($conf)) {
			switch ($conf)
			{
				case 'dtf':
					// we can use only neccessary fields
					if (!empty($auxconf['fields'])) {
						$fields = $auxconf['fields'];
					} else {
						$fields = array('*');
					}
					$conf = array(
						'from' => array(
							array('c' => $this->_name),
							array_merge( $fields ,
								array('dtf' => new Zend_Db_Expr("DATE_FORMAT(" . $this->cprefix . "dt, '%d.%m.%Y')"), 'dts' => new Zend_Db_Expr("UNIX_TIMESTAMP(" . $this->cprefix . "dt)"))
							)
						)
					);
					break;
				default: return $conf;
			}
		}
		return $conf;
	}


	/**
	* Подготовка конфигурации для SQL выборки (NLS)
	* @param string $conf
	* @return array
	*/
 	public function confNLS($conf)
	{
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($conf, 1) . "</textarea><br>";

		$a = array(
			'from' => array(
				array('p' => $this->_name),#$this->_name,
				'p.*'
			),
			'join' => array(
				'name' => array('c' => $this->aNLS['name']),
				'cond' => 'p.id = c.parent_id',
				'cols' => $this->aNLS['cols'], // NB! not neccessaary if c.*
			)
		);

		// empty action - default configuration
		if (empty($conf)) {
			$conf = $a;

		// custom configuration
		} elseif (!is_array($conf)) {
			switch ($conf) {
			case 'dtf':
				$conf = array(
					'from' => array(
						array('p' => $this->_name),#$this->_name,
						array('p.*', 'dtf' => new Zend_Db_Expr("DATE_FORMAT(p.dt, '%d.%m.%Y')"), 'dts' => new Zend_Db_Expr("UNIX_TIMESTAMP(p.dt)"))
					),
					'join' => array(
						'name' => array('c' => $this->aNLS['name']),
						'cond' => 'p.id = c.parent_id',
						'cols' => $this->aNLS['cols'],
					)
				);
				break;
			default: return $conf;
			}

		// $conf is array!
		} else {

			// merge by default!
			if (empty($conf['skipMerge'])) {
				$conf = array_merge($a, $conf);
			}
		}
		return $conf;
	}
	
	/**
	 * @param string $what
	 * @param integer $id
	 * @param boolean $isreg
	 * @return integer
	 */
	function getCountBy($what, $id, $isreg = true)
	{
		if (is_array($what)) {$isreg = false;}
				
		if ($isreg)
		{
			$reg = Zend_Registry::getInstance();
	
			if (isset($reg[$what . 'Count']) && isset($reg[$what . 'Count'][$id]) ) { // cache in registry (or better cache in SQL?)
				return $reg[$what . 'Count'][$id];
			}
		}

		$select = $this->select()
			->from($this->_name, 'COUNT(id) AS cnt')
			->where('flag_status=1');
			
		if (is_array($what)) {
			foreach ($what as $k => $v) {
				$select = $select->where($v . '_id=?', $id[$k]);
			}
		} else {
			$select = $select->where($what . '_id=?', $id);
		}				
		$row = $this->fetchRow($select)->toArray();

		if ($isreg) {$reg[$what . 'Count'][$id] =  $row['cnt'];}

		return $row['cnt'];
	}

	/**
	* WHERE with additional conditions (eg. RPN closed content)
	* @param string $condition
	* @param boolean $first - place condition first 
	* @return string
	*/
	function getWhere($condition = null, $first = true)
	{
		$conditionWhere = $this->conditionWhere;

		// conditional (eg. $this->Content->conditionWhere = 'flag_class=0'; // показывать только открытые материалы
		if ( !empty($conditionWhere) ) {
			if ($first) {
				$where = $condition . ' AND ' . $this->conditionWhere;
			} else {
				$where = $this->conditionWhere . ' AND ' . $condition;
			}
		} else {
			$where = $condition;
		}
		return $where;
	}


	/**
	* Incremental WHERE with additional conditions (eg. RPN closed content)
	* @param string $condition
	* @return string
	* @deprecated 6/8/2009 (you don't need this!)
	*/
	function getWhereAnd($condition = null, $clear = false)
	{
		if ($clear) {
			$this->generalWhere = '';
		}

		$conditionWhere = $this->conditionWhere;
		$generalWhere = $this->generalWhere;

		// conditional (eg. $this->Content->conditionWhere = 'flag_class=0'; // показывать только открытые материалы
		if ( !empty($conditionWhere) && empty($generalWhere) ) {
			$where = $conditionWhere . ' AND ' . $condition;
		} else {
			$where = $condition;
		}

		$generalWhere = $this->generalWhere;
		if (!empty($generalWhere)) {
			$where = $this->generalWhere . ' AND ' . $where;
		}

		$this->generalWhere = $where;

		return $where;

	}

	/**
	* Get Zend_Db_Table_Select
	* @param string $where условие / id / code
	* @param array $conf
	* @return Zend_Db_Table_Rowset or FALSE
	*/
	function getSelect($where = '', $conf = array())
	{
		// replace where with conf ;)
		if (empty($conf) && is_array($where)) {
			$conf = $where;
			$where = '';
		} else {
			// обработка тройственного параметра :)
			if (!empty($where)) {
				if (strpos($where, ' ') === false) {
					if (is_numeric($where)) {
						$where = "id = '" . $where . "'";
					} else {
						$where = "code = '" . $where . "'";
					}
				}
			}
		}
		l($where, __METHOD__. ' where', Zend_Log::DEBUG);
		l($conf, __METHOD__ . ' conf (initial)', Zend_Log::DEBUG);
		#if (empty($conf)) {
		$conf = $this->confSQL($conf); // confSQL must be applied in both cases!
		#} else {
			// TODO: confSQL must be applied in both cases!
			// сейчас ситуация такая - если конфиг уже есть, он должен быть готов к NLS, если это требуется!
		#}
		l($conf, __METHOD__ . ' conf (final)', Zend_Log::DEBUG);

		$select = $this->setSQL($where, $conf);
		l($select, __METHOD__. ' select', Zend_Log::DEBUG);
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($select->__toString(), 1) . "</textarea><br>";die;

		return $select;
	}


	/**
	* Получить 1 запись контента
	* Универсальный метод
	* @param string $where условие / id / code
	* @param array $conf
	* @return Zend_Db_Table_Row or FALSE
	*/
	public function getRow($where = '', $conf = array())
	{
		// special case
		if ($where instanceof Zend_Db_Table_Select)
		{
			$select = $where;
		} else {
			if (empty($conf) && is_array($where)) {$conf = $where; $where = '';}
			$select = $this->getSelect($where, $conf);
			if (!empty($conf['select'])) {return $select;} // compatibility patch
		}

		#d($select);
		$row = $this->fetchRow($select);// Zend_Db_Table_Row
		#d($row);

		if (empty($row)) {return false;}

        // Преобразовываем Zend_Db_Table_Row в массив
        // $res = $row->toArray();

		if (!empty($conf['array'])) { // returns all data as an array
			return $row->toArray();
		} else {
			return $row;
		}
	}


	/**
	* Записи контента - изворотливая выборка
	* @param string $where условие / id / code
	* @param array $conf
	* @return Zend_Db_Table_Rowset or Zend_Paginator or FALSE
	* @todo
	*/
	public function getRows($where = '', $conf = array())
	{
		// special case
		if ($where instanceof Zend_Db_Table_Select)
		{
			$select = $where;
		} else {
			if (empty($conf) && is_array($where)) {$conf = $where; $where = '';}
			$select = $this->getSelect($where, $conf);
			if (!empty($conf['select'])) {return $select;} // compatibility patch
		}
		#d($select, 0);

		if ( (!empty($conf['paginator'])) ) { $this->setPaginator($conf['paginator']); }
		if ( $this->isPaginator && (!empty($conf['returnPaginator'])) ) { $this->isReturnPaginator = true; }# else { $this->isReturnPaginator = false; }
		$rows = $this->paginator($select);
		#d(count($rows), 0);
		#d($rows);
		#d($this->getPaginator());
		if ( $this->isStoredRows || !empty($conf['store']) ) {$this->_storeRows($rows);}
		if ( $this->isReturnPaginator ) {return $this->getPaginator();}

		if (!count($rows)) {return false;}
		
		if (!empty($conf['array']))
		{ // returns all data as an array (DEPRECATED SINCE 4/28/2009)
			return $rows->toArray();
		} else {
			return $rows;
		}
	}

	/**
	 * getRows() wrapper
	 * @param <type> $where
	 * @param <type> $conf
	 * @return <type>
	 */
	public function fetchPaginator($where = '', $conf = array())
	{
		$this->setReturnPaginator(true);
		return $this->getRows($where, $conf);
	}


	/**
	* Компоновка SQL запроса (Zend_Db_Table_Select wrapper)
	* @param string $where
	* @param array $conf
	* @return
	*/
	protected function setSQL($where = '', $conf = array())
	{
		if (empty($conf) && is_array($where)) {
			$conf = $where;
			$where = '';
		}
		
		$NLS = $this->NLS;
		if ($NLS && !empty($conf['skipNLS'])) {$NLS = false;}
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($NLS, 1) . "</textarea><br>";die;

		$select = $this->select();

		if (!empty($conf['join'])) {
			$select = $select->setIntegrityCheck(false); // to avoid 'Select query cannot join with another table' error (ZF-2925)
		}
		#$select = $this->getAdapter()->select();

		// FROM
		if (!empty($conf['from'])) {
			$select = $select->from($conf['from'][0], $conf['from'][1]); // Retrieving specific columns (CAN BE JOIN)
		} elseif (!empty($conf['fields'])) {
			$select = $select->from($this->_name, $conf['fields']); // Retrieving specific columns (NO JOIN)
		}

		// JOIN
		if (!empty($conf['join'])) {
			$join = $conf['join'];
			$select = $select->join(
				$join['name'],
				$join['cond'],
				(isset($join['cols']) ? $join['cols'] : '*'),
				(isset($join['schema']) ? $join['schema'] : null)
			);
		}

		//--< WHERE
		if (empty($where) && !empty($conf['where'])) {$where = $conf['where'];}
		l($where, __METHOD__ . ' where (initial)', Zend_Log::DEBUG);
/*
		// convert non-NLS where to NLS
		if (!empty($where) && $NLS) {
			if (strpos($where, '.') === false) {
				$where = "p." . $where;
			}
		}
*/
		// WHERE: ID IN () - выборка зависимых записей (см. zf_ac - выборка авторов для роликов)
		if ( !empty($conf['in']))
		{
			#echo 'DEBUG:<br><textarea rows=10 cols=100>' . print_r($conf['in'], 1) . '</textarea><br>'; die;
			if(!empty($conf['in']['rows']))
			{
				if ($conf['in']['rows'] instanceof Zend_Paginator)
				{
					$rows = $conf['in']['rows']->getCurrentItems()->toArray();
				} else {
					$rows = $conf['in']['rows']->toArray();
				}
				$id = array();
				foreach ($rows as $row)
				{
					$id[$row[$conf['in']['field']]] = 1;
				}
				$where[] = 'id IN (' . implode(',', (array_keys($id))) . ')';
			}
		}

		// array to string
		if (!empty($where) && is_array($where)) {
			$where = implode(' AND ', $where);
		}
		l($where, __METHOD__ . ' where (intermediate)', Zend_Log::DEBUG);

		// NB! conditionWhere eg. RPN-2: $this->Content->conditionWhere = 'flag_class=0';
		$where = $this->getWhere($where);
		l($where, __METHOD__ . ' where (final)', Zend_Log::DEBUG);

		if (!empty($where)) {
			$select = $select->where($where);
		}

		// additional: keep in mind flag_status
		if (!isset($this->woFlagStatus)) {
			#if (!empty($where)) {$where .= ' AND ';}
			if ($NLS) {
				$w = "p.flag_status = 1 AND c.nls_id = '" . $this->NLSId . "'";# AND c.flag_status = 1
			} else {
				$w = $this->cprefix . 'flag_status = 1';
				if (!empty($conf['join'])) {$w .= ' AND ' . $this->pprefix . 'flag_status = 1';}
			}
			$select = $select->where($w);
		}
		//-->

		// GROUP BY
		if (!empty($conf['group'])) {
			$select = $select->group($conf['group']);
		}

		// ORDER BY
		if (!empty($conf['order'])) { // can be array
			if ($conf['order'] === true) {
				$select = $select->order('flag_order');
			} else {
				$select = $select->order($conf['order']);	
			}
			
		}

		// LIMIT
		if (!empty($conf['limit'])) {
			$select = $select->limit($conf['limit']);
		}

		if ( $this->debug || !empty($conf['debug']) ) {
			echo "DEBUG:<br><textarea rows=5 cols=100>" . print_r($select->__toString(), 1) . "</textarea><br>";
		}

		return $select;
	}


	/**
	* Set default search parameters
	* @param string $search
	* @return void
	*/
	function resetSearch($search)
	{
		$this->search['search'] = $search;

		#if (empty($this->search['fields'])) {
			$this->search['fields'] = array('id','title');
		#}

		//todo: fluid where!
		#if (empty($this->search['where'])) {
			$this->search['where'] = "( flag_status = 1 AND (title LIKE '%" . $search . "%' OR txt LIKE '%" . $search . "%') )";
		#}

		#if (empty($this->search['order'])) {
			$this->search['order'] = 'id DESC';
		#}
	}


	/**
	* Set search parameters
	* @param string $k
	* @param mixed $v
	* @param boolean $append
	* @return void
	*/
	function setSearch($k, $v, $append = false)
	{
		if (!empty($this->search[$k]))
		{
			if ($append)
			{
				$this->search[$k] .= ' ' . $v;
			} else {
				$this->search[$k] = $v;
			}

		} else {
			$this->search[$k] = $v;
		}
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->search[$k], 1) . "</textarea><br>";die;
	}


	/**
	* @uses $res = $this->Projects->getSearch($search);
	* @param string $search
	* @return array
	*/
	function getSearch($search = '')
	{
		// basic way - init search, go search
		if (!empty($search )) {
			$this->resetSearch($search);

		// extended way - search was initialized early (resetSearch + setSearch)
		} else {
			if (empty($this->search['search']))
			{
				return false;
			}
		}

		// Zend_Db_Select
		$select = $this->getAdapter()->select()
		->from($this->_name, $this->search['fields'])
		->where($this->search['where'])
		->order($this->search['order']);

		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($select->__toString(), 1) . "</textarea><br>";die;

		// Получение массива данных
		#$stmt = $this->getAdapter()->query($select);
		#$res = $stmt->fetchAll();
		$res = $this->getAdapter()->fetchAll($select);#, null, Zend_Db::FETCH_ASSOC

		return $res;
	}

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $res = null)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        } else {
			if (isset($this->$name))
			{
				return $this->$name;
			}
		}
        return $res;
    }

	/**
	* Magic function so that $obj->value will work.
	* @param string $name
	* @return mixed
	*/
    public function __get($name)
    {
        return $this->get($name);
    }

	/**
	* Setting of a property
	* @param  string $name
	* @param  mixed  $value
	* @return void
	*/
    public function __set($name, $value)
    {
		$this->_data[$name] = $value;
    }

	/**
	 * Store rows to registry
	 * @param mixed $rows
	 */
	protected function _storeRows($rows)
	{
		if (empty($rows))
		{
			return false;
		}

		if ($rows instanceof Zend_Paginator)
		{
			$rows = $this->_paginator->getCurrentItems();
		}

		#$a = $rows->toArray();
		$name = get_class($rows->getTable()) . 'Rows'; // eg. UsersRows
		#d($name);

		Zend_Registry::set($name, $rows);
		#$res = Zend_Registry::get($name);
		#d($res);
		return true;

		// TODO: add rows into rowset!
		if (Zend_Registry::isRegistered($name))
		{
			$res = Zend_Registry::get($name);
		} else {
			Zend_Registry::set($name, $rows);
			#$res = Zend_Registry::get($name);
			#d($res);
			return true;
		}
		d($res);

		// rewrite data
		foreach ($a as $k => $v)
		{
			$res[$v['id']] = $v;
		}

		Zend_Registry::set($name, $res);
		return true;
		#d(Zend_Registry::get($name));
	}

	/**
	 * Wake-up from registry
	 */
	static function row($id, $name = null)
	{
		if ($name === null)
		{
			$name = get_class($this);
		}
		
		$name .= 'Rows';
		#d($name);

		if (Zend_Registry::isRegistered($name))
		{
			$rows = Zend_Registry::get($name);

			foreach ($rows as $row)
			{
				if ($row->id == $id)
				{
					return $row;
				}
			}
		}
		
		// TODO: fetch row by id and class!!!
		return false;
	}

	/**
	 * @param integer $id
	 * @param string $field
	 * @return mixed
	 */
	function getById($id, $field = '')
	{
		if (!is_numeric($id)) {return false;}

		$select = $this->select()->where('flag_status=1');
		if (!empty($field)) {
			$select = $select->where($field . '_id=?', $id);
		} else {
			$select = $select->where('id=?', $id);
		}
			
		$res = $this->fetchRow($select);
		return $res;
	}

	/**
	 * Filter image name
	 * @todo validator/filter method? see http://www.zfforums.com/zend-framework-components-13/core-infrastructure-19/add-filter-zend-form-file-element-3713.html
	 * @param string $s
	 */
	static function filterFilename($s)
	{
		#$lowerCaseFilter = new Zend_Filter_File_LowerCase();
		$s = strip_tags($s);
		$s = stripcslashes($s);
		$res = strtolower($s);
		return $res;
	}

	/**
	* Get full/preview image
	* @param mixed $id
	* @param array $conf
	* @return string
	*/
	function getImage($id, $conf = null)
	{
		$full = isset($conf['full']) ? $conf['full'] : true;#d($full);
		$fs = isset($conf['fs']) ? $conf['fs'] : false;
		$path = isset($conf['path']) ? '/' . $conf['path'] : '';

		// named image! since 04/18/10
		if ( isset($conf['named']) || is_string($full))
		{
			$s = self::filterFilename($full);
			if (is_array($id)) {
				$fn = $s . '_' . $id[0] . '_' . $id[1]; // addition images, eg lalalala_1_1.jpg
			} else {
				$fn = $s . '_' . $id; // image, eg lalalala_1.jpg
			}

		// std way - ID-generated image name
		} else {
			if (is_array($id)) {
				#$fn = sprintf("%0" . $this->imgs['length'] . "d", $id[0]) . '_' . sprintf("%02d", $id[1]); // addition images, eg 00000010_02.jpg
				$fn = Site::zerofill($id[0], $this->imgs) . '_' . sprintf("%02d", $id[1]); // addition images, eg 00000010_02.jpg
			} else {
				#$fn = sprintf("%0" . $this->imgs['length'] . "d", $id);
				$fn = Site::zerofill($id, $this->imgs);
			}
		}

		// suffix (eg '.s' for small images etc)
		if (!empty($conf['suffix']))
		{
            $fn .= $conf['suffix'];#$fn .= '.' . $conf['suffix'];
        }

      	$fn .= $this->imgs['ext'];

		$name = $this->_name;

		$fo = $this->imgs['folder'];#d($fo);


      	if ( $this->imgs['folderFiles'] > 0 )
		{
			$n = round($id / $this->imgs['folderFiles']);
			if ( $this->imgs['folderFill'] > 0) {
                $n = sprintf("%0" . $this->imgs['folderFill'] . "d", $n);
            }
           	$fo .=  '/' . $n;
		}

		#'folderSubfolders' => null, // max subfolder per folder
		#'folderFill' => 4, // zero fill symbols


		 $fo .= $path;

		// fullsize picture
		if ($full) {
			$s = $fo . "/";
			if ($this->imgs['prefixes']) {
				$s .= $name . $s;
			}

		// preview picture
		} else {
			$s = $fo . '/previews/';
			if ($this->imgs['prefixes']) {
				$s .= 'previews' . $s;
			}
		}

		// virtual path
		$s = 'images/' . $s . $fn;

		// fs path
		if ($fs) {
			$s = PATH_PUB . $s;
		} else {
			$s = '/' . $s;
		}

		return $s;
	}

	/**
	* http://www.nabble.com/Zend_Db::insertSelect()---INSERT-INTO-...-SELECT---td20824639.html
	* @todo
	* @param
	* @return
	*/
	protected function insertSelect($fields = array(), Zend_Db_Select $select)
	{
		$fieldString = '';
		if (count($fields))
		{
			foreach($fields as $fieldKey => $field)
			{
				$fields[$fieldKey] =  $this->quoteIdentifier($field);
			}

			$fieldString = ' (' . implode(',', $fields) . ')';
		}

		$query = "INSERT INTO " . $this->quoteIdentifier($tableName) . $fieldString . " " . $select;
		$this->_db->query($query);
	}
	
	
	/**
	* Fetch row by id
	* @return mixed
	*/
	function fetchById($id)
	{
		$rows = $this->find($id);
		if (!$rows) {return false;}
		$row = $rows->current();
		return $row;
	}
/*
	function isPaginator()
	{
		return $this->isPaginator;
	}
*/

	function getPaginator()
	{
		return $this->_paginator;
	}

	function setPaginator($v = true)
	{
		$this->isPaginator = (bool) $v;
	}

	function setReturnPaginator($v = true)
	{
		$this->isReturnPaginator = (bool) $v;
		if ($this->isReturnPaginator) {
			$this->setPaginator(true);
		}
	}

	function setStoredRows($v = true)
	{
		$this->isStoredRows = (bool) $v;
	}

	/**
	* Set Zend_Paginator
	* @param Zend_Db_Table_Select $select
	* @return Zx_Db_Table_Rowset
	*/
	function paginator($select)
	{
		if (!$this->isPaginator)
		{
			$rows = $this->fetchAll($select);
			return $rows;
		}
		$this->_paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbTableSelect($select));
		$this->_paginator->setItemCountPerPage($this->ItemCountPerPage);#$this->get('ItemCountPerPage')
		$this->_paginator->setCurrentPageNumber(Zend_Registry::get('page'));
		#d($this->_paginator);

		#$this->pagescount = $this->_paginator->count();
		#Zend_Registry::set('pages_count', $this->pagescount); // YAGNI! see partials/paginator.phtml

		// return paginator ot rowset!
		if ($this->isReturnPaginator)
		{
			return $this->_paginator;
		} else {
			$rows = $this->_paginator->getCurrentItems();
			#Zend_Registry::set('rows_count', $rows->count()); // why for?
			return $rows;
		}
	}

	function updateHits($id)
	{
		$data = array(
			'stat_hits' => new Zend_Db_Expr('stat_hits+1')
		);
		$where = $this->getAdapter()->quoteInto('id = ?', $id);
		return $this->update($data, $where);
	}


	/**
	 * insert/update & upload
	 */
	protected function _updateData($data = array(), $conf = null)
	{
		$res = false;

		if (!empty($data['id']))
		{
			$where = $this->getAdapter()->quoteInto('id = ?', $data['id']);
			$res = $this->update($data, $where);
			$row = $this->getById($data['id']); // NB! get updated row always!
		} else {
			if (!empty($conf['test'])) {
				$res = 1;//TEST!
		  	} else {
				$res = $this->insert($data);
			}
			if ($res) {
				$row = $this->getById($res);
			}
		}

		if ($res) {
			$this->setN(FrontEnd::getMsg(array('update', 'ok')), 'success');
		} else {
			$this->setN(FrontEnd::getMsg(array('update', 'fail')), 'errors');
		}

		#d($row);
		if (FrontEnd::isUpload() && $row)
		{
			$resu = $row->upload();#d($res);
		}

		// particular post-update manipulations
		#d($row);
		if ($row) {
			$res = $row->_postUpdate();
		}

		return $res;
	}

	/**
	 * Set notification via Flash Messanger
	 * @param string $s
	 */
	static public function setN($s, $ns = 'default')
	{
		$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		$flashMessenger->setNamespace($ns)->addMessage($s);
	}

}