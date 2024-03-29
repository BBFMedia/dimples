<?php

/**
 * Base Dimples class
 * @package Dimples
 *
 */
global $plural_rules;
$plural_rules = array('/(x|ch|ss|sh)$/' => '\1es', # search, switch, fix, box, process, address 
    '/series$/' => '\1series',
    '/([^aeiouy]|qu)ies$/' => '\1y',
    '/([^aeiouy]|qu)y$/' => '\1ies', # query, ability, agency 
    '/(?:([^f])fe|([lr])f)$/' => '\1\2ves', # half, safe, wife 
    '/sis$/' => 'ses', # basis, diagnosis 
    '/([ti])um$/' => '\1a', # datum, medium 
    '/person$/' => 'people', # person, salesperson 
    '/man$/' => 'men', # man, woman, spokesman 
    '/child$/' => 'children', # child 
    '/(.*)status$/' => '\1statuses',
    '/s$/' => 's', # no change (compatibility) 
    '/$/' => 's'
        );

function getSingular($word)
{
    $singular = array(
        '/(quiz)zes$/i' => '\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias|status)es$/i' => '\1',
        '/([octop|vir])i$/i' => '\1us',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/(bus)es$/i' => '\1',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1ovie',
        '/(s)eries$/i' => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/([^f])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(n)ews$/i' => '\1ews',
        '/s$/i' => '',
    );

    $uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

    $irregular = array(
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves');

    $lowercased_word = strtolower($word);
    foreach ($uncountable as $_uncountable) {
        if (substr($lowercased_word, (-1 * strlen($_uncountable))) == $_uncountable) {
            return $word;
        }
    }

    foreach ($irregular as $_plural => $_singular) {
        if (preg_match('/(' . $_singular . ')$/i', $word, $arr)) {
            return preg_replace('/(' . $_singular . ')$/i', substr($arr[0], 0, 1) . substr($_plural, 1), $word);
        }
    }

    foreach ($singular as $rule => $replacement) {
        if (preg_match($rule, $word)) {
            return preg_replace($rule, $replacement, $word);
        }
    }

    return $word;
}

function getplural($word)
{

    $result = $word;
    global $plural_rules;
    foreach ($plural_rules as $pattern => $repl) {
        $result = preg_replace($pattern, $repl, $word);

        if ($result != $word)
            break; // leave if plural found
    }

    return $result;
}

function can_load($class)
{
    if (function_exists('can_auto_load')) {
        $file = can_auto_load($class);
        if (file_exists($file))
            require_once($file);
    }
    return class_exists($class);
}

require_once "db.class.php";

class TDBBase {

    private $_data = null;
    private $_index = -1;
    public $_id = 'id';
    public $_overRideSelect = '';
    public $_activeJoins = array();
    public $_limit = -1;
    public $_offset = 1;
    public $_orderBy;
    public $_orderDirection = 'ASC';
    public $_where = '';
    public $_groupBy = '';
    public $AutoPageLoad = false;
    public $_lastInsertId = -1;
    public $instance = null;
    public $_table = null;

    function __construct()
    {
        //  parent::__construct();
    }

    /**
     * an array of joins. must be re delared in you inherited class with all the joins
     *  join scheme 
     *  <code>
     *  public $_joins =   array(
      '{join_name}' => array('table'=> '{table}' ,'alias'=> '{alias}','on'=>'{on_of_join} ','select' => array({array_of_fields})) ,
      'framestatus' => array('table'=> 'statuses' ,'alias'=> 'frame_status','on'=>'masterlists.frame_status_id = frame_status.id ','select' => array('frame_status')) ,

     *  </code>
     *   frame status will create a join that look like  left join statuses frame_status on  masterlists.frame_status_id = frame_status.id
     * and adds to select   frame_status.frame_status
     */
    public $_joins = array();

    function GetSelect()
    {
        if (empty($this->_overRideSelect)) {
            foreach ($this->_activeJoins as $item) {
                $item['alias'] = (isset($item['alias']) ? $item['alias'] : $item['table']);
                foreach ($item['select'] as $sl) {
                    $selects[] = $item['alias'] . '.' . $sl;
                }
                if (empty($item['select']))
                    $selects[] = $item['alias'] . '.*';
            }
            $selects[] = $this->getTable() . '.*';
            $select = 'SELECT ';

            foreach ($selects as $sl) {
                $select .= $sl . ' , ';
            }
            return subStr($select, 0, strLen($select) - 2);
            ;
        }
        else
            return 'SELECT ' . $this->_overRideSelect;
    }

    function getTable()
    {

        $table = $this->_table;

        if ($table == null) {
            $table = get_class($this);
            $table = strtolower(subStr($table, 3, 100));
        }
        return $table;
    }

    function GetFrom()
    {


        $table = $this->getTable();

        return 'FROM ' . $table;
    }

    function GetJoin()
    {
        $join = '';

        foreach ($this->_activeJoins as $item) {
            // the on actualy does not work because of no singular convertion
            if (empty($item['on']))
                $item['on'] = '`' . $this->getTable() . '`.`' . $item['table'] . '`_id = `' . $item['table'] . '`.`id` ';
            if (empty($item['type']))
                $item['type'] = 'LEFT';
            $join .= ' ' . $item['type'] . ' JOIN ' . $item['table'] . ' ' . $item['alias'] . ' ON ' . $item['on'];
        }
        return $join;
    }

    function GetWhere($where)
    {
        if (!empty($where))
            return 'WHERE ' . $where;

        return '';
    }

    function GetLimit()
    {
        if ($this->_limit > -1) {
            return 'LIMIT ' . ($this->_offset)
                    . ' , ' . $this->_limit;
        }
    }

    function GetOrderBy()
    {
        if (!empty($this->_orderBy)) {
            return 'ORDER BY ' . $this->_orderBy . ' ' . $this->_orderDirection;
        }
        return '';
    }

    function GetGroupBy()
    {
        if (!empty($this->_groupBy)) {
            return 'GROUP BY ' . $this->_groupBy;
        }
        return '';
    }

    function findFirst($where, $lazy = false)
    {
        $this->find($where, $lazy);
        return $this->next();
    }

    function find($where, $lazy = false)
    {

        $sql = $this->buildsql($where, false);
        $this->_data = $this->query($sql, true, false, $lazy);
        return $this;
    }

    function tableCount($where)
    {

        $sql = $this->buildsql($where, true);
        $count = $this->query($sql, false);
        return $count[0][0];
    }

    function buildsql($where, $count = false)
    {
        if (is_numeric($where)) {
            $where = ' ' . $this->getTable() . '.' . $this->_id . ' = "' . $this->escape($where) . '" ';
        }
        $this->_where = $where;
        if ($count) {
            $sql = 'select count(*) ';
        } else {
            $sql = $this->GetSelect();
        }
        $sql .= ' ' . $this->GetFrom();
        $sql .= ' ' . $this->GetJoin();
        $sql .= ' ' . $this->GetWhere($this->_where);
        $sql .= ' ' . $this->GetGroupBy();
        if (!$count) {

            $sql .= ' ' . $this->GetOrderBy();
            $sql .= ' ' . $this->GetLimit();
        }
        $this->_index = -1;


        return $sql;
    }

    function nextPage()
    {

        $this->_offset = $this->_offset + $this->_limit;
        return $this->find($this->_where);
    }

    function limit($offset, $limit, $AutoPageLoad = 0)
    {
        $this->_limit = $limit;

        $this->_offset = $offset;
        // if ($AutoPageLoad != -1)
        $this->AutoPageLoad = $AutoPageLoad;
        return $this;
    }

    function page($page, $pageSize, $AutoPageLoad = 0)
    {
        $limit = $pageSize;
        $offset = ($page - 1) * $pageSize;
        return $this->limit($offset, $limit, $AutoPageLoad);
    }

    function ReturnAll()
    {
        return $this->_data;
    }

    function fetchAll()
    {
        return $this->_data;
    }

    function getCurrentData()
    {
        return $this->_data[$this->_index];
    }

    function count()
    {
        return count($this->_data);
    }

    function next()
    {

        //if empty then now data in avalibel
        if (empty($this->_data)) {
            return false;
        }
        // inc index to next record
        $this->_index += 1;
        // if index is at end of data array then..
        // 1) if AutoPageLoad then try load next page
        // 2) if !AutoPageLoad then return false
        if ($this->_index >= count($this->_data)) {
            if (($this->AutoPageLoad) and ($this->_limit > -1)) {
                $this->nextPage();
                return ($this->next());
            }
            else
                return false;

            return false;
        }
        //else just return next record
        return $this->getCurrentData();
    }

    function join($join)
    {

        // if only one join as a string create an array
        if (!is_array($join))
            $join = array($join);

        // if mutiple joins have been added by refenced name then added each join by name  
        if (!isset($join['table'])) {
            foreach ($join as $joinname)
                $this->_activeJoins[] = $this->_joins[$joinname];
        } else {
            /// if it is a complete join decloration then add directly
            $this->_activeJoins[] = $join;
        }

        return $this;
    }

    function select($select)
    {
        // set _overRideSelect with a new select. will not use default
        $this->_overRideSelect = $select;
        return $this;
    }

    function order($field, $direct = '')
    {
        $this->_orderBy = $field;
        $this->_orderDirection = $direct;
        return $this;
    }

    function group($group)
    {
        $this->_groupBy = $group;
        return $this;
    }

    function createFieldList($data)
    {
        $sql = '';
        foreach ($data as $key => $item) { // mysql_real_escape_string
            $sql .= ' `' . $key . '` = "' . $this->escape($item) . '" ,';
        }
        $sql = trim($sql, ',');
        return $sql;
    }

    function createInsert($data)
    {

        $sql = $this->createFieldList($data);
        $sql = 'INSERT INTO  ' . $this->getTable() . ' set ' . $sql;

        return $sql;
    }

    function createUpdate($data, $where)
    {
        $sql = $this->createFieldList($data);
        $sql = 'UPDATE  ' . $this->getTable() . ' SET ' . $sql . ' where ' . $where;

        return $sql;
    }

    function insert($data)
    {
        $sql = $this->createInsert($data);
        return $this->update($sql);
    }

    function describe($tablename = '')
    {
        if (empty($tablename))
            $tablename = $this->getTable();
        $sql = 'DESCRIBE ' . $tablename;
        $data = $this->query($sql, true, false, true);
        return $data;
    }

    function update($sql, $lazy = false)
    {
        $exetime = db::getMicroTime();  
		$instance ='';
        if(isset($this))
		  $instance =$this->instance ;
        $effectedRows = db ::getInstance($instance)->exec($sql);
        db::addQuery($sql, $exetime);

        if (isset($this))
            $this->_lastInsertId = db::getInstance($this->instance)->lastInsertId();

        return $effectedRows;
    }

    function lastInsertId()
    {
        return $this->_lastInsertId;
    }

    function query($sql, $usefieldnames = false, $idaskey = false, $lazy = false)
    {

        //  slBug($sql, 'SQL' . ($lazy?' Lazy':''));
         if (isset(db ::getInstance($instance)->mock))
             return db ::getInstance($instance)->mockquery($sql, $usefieldnames = false, $idaskey = false, $lazy = false); 

        $exetime = db::getMicroTime();
        /*     if ($lazy)
          $rs = db :: lazy_prepare($sql);
          else */
       	$instance ='';
        if(isset($this))
		  $instance =$this->instance ;
        
        $rs = db ::getInstance($instance)->prepare($sql);

        // if a pdo prepare returns an array it means it is a mockobject
        // mock objects return right away
        if (is_array($rs))
        {
            return $rs;
        }
        if (!$rs) {
            echo $sql;

            throw new TException('Invalid query: ' . $rs->errorInfo());
        }
        $rs->execute();
        db::addQuery($sql, $exetime);


        if ($rs === false)
            return false;

        $rr = array();

        if ($usefieldnames) {
            while ($arr = $rs->fetch(PDO :: FETCH_ASSOC)) {
                if (!$idaskey) {
                    $rr[] = $arr;
                } else {
                    $rr[reset($arr)] = $arr;
                }
            }
        } else {
            while ($arr = $rs->fetch(PDO :: FETCH_NUM)) {
                if (!$idaskey) {
                    $rr[] = $arr;
                } else {
                    $rr[$arr[0]] = $arr;
                }
            }
        }
        // fb('Trace Label', FirePHP::TRACE);
        //  slBug(array_merge(array($fields), $rr), 'Data ' . ($exetime) . ' secs', FirePHP :: TABLE);
        return $rr;
    }

    static function escape($str)
    {
        $slashed = addslashes($str);
        return $slashed;
    }

}

