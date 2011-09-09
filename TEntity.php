<?php


class TEntity {

 private $orgData =array();
 private $changed = array();

function setfieldData($data)
{
  foreach($data as $key => $item)
      $this->orgData[ $key] = array('data_type'=>'field', 'value'=>$item);
}

public function __set($index, $value)
 {
      if (($this-> __get($index) <>  $value) or ($value == ''))
	$this->changed[$index]['value'] = $value;
 }

public function __get($index)
 {

     if (isset($this->changed[$index]['value']))
       return $this->changed[$index]['value'];


     return $this->orgData[$index]['value'];
 }
public function setType($index,$type)
 {
 
 
     if ($this->orgData[$index]['data_type'] <> $type)
     {
      if (!isset( $this->changed[$index]['value'] ))
       $this->changed[$index]['value']  = $this->orgData[$index]['value'];
       $this->changed[$index]['data_type']  = $type;
       }
 } 
public function setMeta($index,$meta)
 {
 
 
     if ($this->orgData[$index]['meta'] <> $meta)
     {
            $this->changed[$index]['meta']  = $meta;
       }
 } 
public function getFieldList($exp)
                 {

                 $result = array();
                 foreach( $this->orgData  as $key =>$value)
                   {
                    if (preg_match($exp,$key,$matches))
                      $result[ $key ] = $value;
                   }
                 //replace with changed is exists
                 foreach( $this->changed  as $key =>$value)
                   {
                    if (preg_match($exp,$key,$matches))
                      $result[ $key ] = $value;
                   }
                   return $result;
                 }
 function loadMetaData()
 {

       $db = new TDBMetaData()  ;
       
       $rs = $db->getMetaData( $this->guid);
       if (!empty($rs ))
         $this->orgData = $rs + $this->orgData ;

    
 }
 function getTable()
 {
    return (getplural(subStr(get_class($this),1,1000)));
 
 }
 function getDBClass()
 {
    return 'TDB'.ucfirst($this->getTable());
 
 }
 function load($guid)
 {
      $classname = $this->getDBClass();
 
    if(class_exists( $classname ))
    {
        $db = new $classname();
      
    }
    else
      {
      $db =new TDBEntities();
     // $db->_table =  $this->getTable();
      }
     $data = $db->getEntity($guid);

     $this->setfieldData($data);
     $this->loadMetaData();
 return $rs;
 }
 protected function save()
 {
 
 
   $classname = $this->getDBClass();
 
   if(class_exists( $classname ))
    {
        $db = new $classname();
      
    }
    else
      {
      $db =new TDBEntities();
      $db->_table =  $this->getTable();
      }
      

   $metaChanges = array();
   $tableChanges = array();
   
   unset($this->changed['guid']);
   unset($this->changed['entity_type']);
   unset($this->changed['guid_a']);
   unset($this->changed['guid_b']);
   unset($this->changed['relation']);
   unset($this->changed['owner']);
   
   foreach($this->changed as  $field => $item)
    {
     if ($item['type'] == 'field')
       {
        $tableChanges[$field] = $item['value'];
       }
       else
       {
        $metaChanges[$field] = $item;
  
       }
       
    }

   if (!empty($tableChanges)) 
   {

    $tableChanges['id'] = $this->guid;
    $guid = $db->saveEntity( $tableChanges);
  
    $this->load($guid);
    }
    
   $mdb = new TDBMetaData();
   
   $mdb->saveMetaData($this->guid,$metaChanges);
   

  foreach($this->changed as $key => $item)
  {
   $this->orgData[$key] =  array_merge( (array)$this->orgData[$key] , $item);
  } 
 $this->changed = array(); 
      
 }
}

