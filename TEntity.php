<?php


class TEntity {

 private $orgData =array();
 private $changed = array();

function setData($data)
{
  $this->orgData = $data;
}

public function __set($index, $value)
 {
      if (($this-> __get($index) <>  $value) or ($value == ''))
	$this->changed[$index] = $value;
 }

public function __get($index)
 {
     if (isset($this->changed[$index]))
       return $this->changed[$index];

     return $this->orgData[$index];
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

     $this->setData($data);
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
      
   $tfields = $db->describe();
   $fields = array();
   foreach($tfields as $field)
      {
      $fields[$field['Field']] = $field;
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
     if (isset($fields[$field]))
       {
        $tableChanges[$field] = $item;
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
  $this->orgData[$key] = $item;
 } 
 $this->changed = array(); 
      
 }
}

