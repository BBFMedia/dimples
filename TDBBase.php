<?php
global $plural_rules;
$plural_rules = array( '/(x�ch�ss�sh)$/' => '\1es', # search, switch, fix, box, process, address
'/series$/' => '\1series',
'/([^aeiouy]�qu)ies$/' => '\1y',
'/([^aeiouy]�qu)y$/' => '\1ies', # query, ability, agency
'/(?:([^f])fe�([lr])f)$/' => '\1\2ves', # half, safe, wife
'/sis$/' => 'ses', # basis, diagnosis
'/([ti])um$/' => '\1a', # datum, medium
'/person$/' => 'people', # person, salesperson
'/man$/' => 'men', # man, woman, spokesman 
'/child$/' => 'children', # child
'/(.*)status$/' => '\1statuses',
'/s$/' => 's', # no change (compatibility)
'/$/' => 's');

function getplural($word)
{
    $result = $word;
    global $plural_rules;
    foreach($plural_rules as $pattern=> $repl)
        {
        $result = preg_replace ($pattern, $repl, $word);
        if ($result!= $word) break; // leave if plural found
        }
    return $result;
}

function can_load($class)
{
    if (function_exists('can_auto_load'))
    {
        $file = can_auto_load($class);
        if(file_exists($file))
        require_once($file);
    }
    return class_exists($class);
}

require_once "db.class.php";
class TDBBase
{
     private $_data = null;
     private $_index = -1;
    
     public $_id = 'id';
     public $_overRideSelect = '';
     public $_joins = array();
     public $_activeJoins = array();
     public $_table = null;
     public $_limit = -1;
     public $_offset = 1;
     public $_orderBy;
     public $_orderDirection = 'ASC';
     public $_where = '';
     public $_groupBy = '';
     public $AutoPageLoad = false;
     public $_lastInsertId = -1;
    
    
function GetSelect()
{
    if (empty($this -> _overRideSelect))
    {
        foreach ($this -> _activeJoins as $item)
        {
             $item['alias'] = (isset($item['alias'])?$item['alias']:$item['table']);
             foreach($item['select'] as $sl)
             {
                $selects[] = $item['alias'] . '.' . $sl;
             }
        }
        $selects[] = $this -> getTable() . '.*' ;
        $select = 'SELECT ';
        foreach($selects as $sl)
        {
             $select .= $sl . ' , ';
        }
        return subStr($select, 0, strLen($select)-2);;
    }
    else
         return 'SELECT ' . $this -> _overRideSelect;
}

    function getTable()
    {
     $table = $this -> _table;
     if ($table == null)
    {
         $table = get_class($this);
         $table = subStr($table, 3, 100);
    }
     return $table;
     }

     function GetFrom()
    {
     $table = $this -> getTable();

     return 'FROM ' . $table;
     }
    function GetJoin()
    {
     $join = '';

     foreach ($this -> _activeJoins as $item)
    {
         // the on actualy does not work because of no singular convertion
        if (empty($item['on']))
             $item['on'] = '`' . $this -> getTable() . '`.`' . $item['table'] . '`_id = `' . $item['table'] . '`.`id` ';
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
     if ($this -> _limit > -1)
    {
         return 'LIMIT ' . ($this -> _offset)
         . ' , ' . $this -> _limit;
         }
     }
    function GetOrderBy()
    {
     if (!empty($this -> _orderBy))
        {
         return 'ORDER BY ' . $this -> _orderBy . ' ' . $this -> _orderDirection;
         }
     return '';
     }
    function GetGroupBy()
    {
     if (!empty($this -> _groupBy))
        {
         return 'GROUP BY ' . $this -> _groupBy ;
         }
     return '';
     }

     function findFirst($where)
    {
     $this -> find($where);
     return $this -> next();
     }

    function find($where)
    {
     if ((preg_match('/[ ]/', $where)) or (empty($where)))
        {
     }
else
    {
     $where = $this -> getTable() . '.' . $this -> _id . ' = "' . $where . '"';
     }
 $this -> _where = $where;
 $sql = $this -> GetSelect();
 $sql .= ' ' . $this -> GetFrom();
 $sql .= ' ' . $this -> GetJoin();
 $sql .= ' ' . $this -> GetWhere($this -> _where);
 $sql .= ' ' . $this -> GetGroupBy();
 $sql .= ' ' . $this -> GetOrderBy();
 $sql .= ' ' . $this -> GetLimit();

 $this -> _index = -1;


 $this -> _data = $this->query($sql, true);
 return $this;

 }
function nextPage()
{

 $this -> _offset = $this -> _offset + $this -> _limit;
 return $this -> find($this -> _where);
 }
function limit($offset, $limit, $AutoPageLoad = 0)
{
 $this -> _limit = $limit;

 $this -> _offset = $offset;
 // if ($AutoPageLoad != -1)
$this -> AutoPageLoad = $AutoPageLoad;
 return $this;
 }
function page($page, $pageSize, $AutoPageLoad = 0)
{
 $limit = $pageSize;
 $offset = ($page - 1) * $pagesize;
 return limit($offset, $limit, $AutoPageLoad);


 }
function ReturnAll()
{
 return $this -> _data;
 }
function fetchAll()
{
 return $this -> _data;
 }
function getCurrentData()
{
 return $this -> _data[$this -> _index];

 }
function count()
{
 return count($this -> _data);
 }
function next()
{
 if (empty($this -> _data))
    {
     return false;
     }
 $this -> _index += 1;
 if ($this -> _index >= count($this -> _data))
    {
     if (($this -> AutoPageLoad) and ($this -> _limit > -1))
        {
         $this -> nextPage();
         return ($this -> next());
         }
    else
         return false;

     return false;
     }
 return $this -> getCurrentData();
 }

function join($join)
{
 if (!is_array($join))
     $join = array($join);

 if (!isset($join['table']))
    {
     foreach($join as $joinname)
     $this -> _activeJoins[] = $this -> _joins[$joinname];
     }
else
    {
     $this -> _activeJoins[] = $join;
     }

 return $this;
 }
function select($select)
{
 $this -> _overRideSelect = $select;
 return $this;
 }
function order($field, $direct = '')
{
 $this -> _orderBy = $field;
 $this -> _orderDirection = $direct;
 return $this;
 }

function group($group)
{
 $this -> _groupBy = $group;
 return $this;
}

function createFieldList($data)
{
     foreach($data as $key => $item)
    {//mysql_real_escape_string
        $sql .= ' `'.$key.'` = "'.($item).'" ,';
    }
    $sql = trim($sql,',');
return $sql;
}

function createInsert($data)
{

    $sql = $this->createFieldList($data);
    $sql = 'INSERT INTO  '.$this->getTable().' set '.$sql;

    return $sql;
}

function createUpdate($data,$where)
{
$sql = $this->createFieldList($data);
$sql = 'UPDATE  '.$this->getTable().' SET '.$sql .' where '.$where;

    return $sql;
}

function insert($data)
{
$sql = $this->createInsert($data);
return $this->update($sql);
}

function save($data,$where)
{
$sql = $this->createUpdate($data,$where);
$this->update($sql);
}

function Describe()
{
$sql = 'DESCRIBE '.$this->getTable();
$data = $this->query($sql,true);
return $data;    
}

function update($sql)
{
 $effectedRows = db::exec($sql);
         $this->_lastInsertId = db::lastInsertId();
 return $effectedRows;
 }

function lastInsertId()
{
return $this->_lastInsertId ;
}

function query($sql, $usefieldnames = false, $idaskey = false)
{
 $exetime = microtime();
 $rs = db::prepare($sql);
 $rs -> execute();
 if(!$rs)
{
     echo $sql;

     throw new TException('Invalid query: ' . $rs -> errorInfo());
     }
 if($rs === false) return $false;

 $rr = array();

 if($usefieldnames)
{
     while($arr = $rs -> fetch(PDO :: FETCH_ASSOC))
     {
         if(!$idaskey)
        {
             $rr[] = $arr;
             }
        else
            {
             $rr[$arr[0]] = $arr;
             }
     }
    }
else
    {
     while($arr = $rs -> fetch(PDO :: FETCH_NUM))
     {
         if(!$idaskey)
        {
             $rr[] = $arr;
             }
        else
            {
             $rr[$arr[0]] = $arr;
             }
      }
     }
// fb('Trace Label', FirePHP::TRACE);
// slBug(array_merge(array($fields),$rr), 'Data '.($exetime) .' secs', FirePHP::TABLE);
return $rr;
}

}
?> 