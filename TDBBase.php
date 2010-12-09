<?php
 //
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
  
  function GetSelect()
  {
  if  (empty($this->_overRideSelect))
  {
 
     foreach ($this->_activeJoins as $item)
   {
         $item['alias'] = (isset($item['alias'])?$item['alias']:$item['table']);
   foreach($item['select'] as $sl)
    {

         $selects[] =  $item['alias'] .'.' .$sl;
     } 
   
   }
   $selects[] =  $this->getTable().'.*' ;
   $select = 'SELECT ';
   foreach( $selects as $sl)
    {
    $select .= $sl .' , '; 
    }
   return subStr($select,0,strLen($select)-2);;
   }
   else
   return 'SELECT '. $this->_overRideSelect;
  }
  function getTable()
  {
   $table = $this->_table;
    if ($table == null)
   {
      $table = get_class($this);
      $table = subStr($table,3,100);
   }
   return $table;
  }
  function GetFrom()
  {
  $table = $this->getTable();

   return 'FROM '.$table;
  }
  function GetJoin()
  {
   $join = '';

   foreach ($this->_activeJoins as $item)
   {
       
       $join .= ' LEFT JOIN '. $item['table'].' '.$item['alias'] .' ON ' .$item['on'];
   }
   return   $join;
  }  
  function GetWhere($where)
  {
if (!empty($where))
   return 'WHERE ' .$where;
   
   return '';
  }  
  function GetLimit()
  {
  if ($this->_limit > -1)
   {
   return 'LIMIT ' .($this->_offset) 
       . ' , ' . $this->_limit;
   }
   }
  function GetOrderBy()
  {
  if (!empty($this->_orderBy ))
   {
   return 'ORDER BY '. $this->_orderBy .' '.$this->_orderDirection;
   }
   return '';
  }  
  function GetGroupBy()
  {
  if (!empty($this->_groupBy ))
   {
   return 'GROUP BY '. $this->_groupBy ;
   }
   return '';
  }  
  function find($where)
  {
  if ((preg_match('/[ ]/',$where) ) or (empty($where)))
    {
     
    }
    else
    {
     $where =   $this->getTable().'.'.$this->_id .' = "'.$where.'"';
    }
  $this->_where = $where;
  $sql = $this->GetSelect();
  $sql .= ' '.$this->GetFrom();
  $sql .= ' '. $this->GetJoin();
  $sql .= ' '. $this->GetWhere( $this->_where);
  $sql .= ' '. $this->GetOrderBy();
  $sql .= ' '. $this->GetGroupBy();
  $sql .= ' '. $this->GetLimit();

   $this->_index  = -1;
   
 
  $this->_data = GetTable($sql,true);
  }
  function nextPage()
  {
  
  $this->_offset = $this->_offset + $this->_limit;  
  return $this->find($this->_where); 
  }
  function limit($offset,$limit, $AutoPageLoad = 0)
  {
  $this->_limit = $limit;
  
  $this->_offset =  $offset;
   // if ($AutoPageLoad != -1)
     $this->AutoPageLoad = $AutoPageLoad;
  return $this;  
  }
  function page($page,$pageSize, $AutoPageLoad = 0)
  {
  $limit = $pageSize;
  $offset = ($page - 1) * $pagesize; 
  return limit( $offset,$limit, $AutoPageLoad);


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
  if (empty($this->_data))
   {
    return false;
   }
  $this->_index += 1;
  if  ($this->_index >= count($this->_data))
   {
   if (($this->AutoPageLoad) and ($this->_limit > -1) )
   {
   $this->nextPage();
   return ($this->next());
   }
   else
   return false; 

   return false;
   } 
  return $this->getCurrentData();
  }
  function join($joinName)
  {
  $this->_activeJoins[] = $this->_joins[$joinName];

  return $this;
  }
  function select($select)
  {
  $this->_overRideSelect = $select;
  return $this;
  }
  function order($field,$direct = '')
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

   function update($sql)
  {
  $rs = DoSQL($sql);
  return  mysql_insert_id();
  ;
  }
  function query($sql,$fieldnames=false,$doID = false)
  {
  $rs = GetTable($sql,$fieldnames,$doID);
  return $rs;
  }
}
?> 