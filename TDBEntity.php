<?php


require_once "TDBBase.php";
function pullGuid(&$entity)
{
 if (!is_numeric($entity))
    {
     $entity = $entity->guid;
    }
   return $entity;
}
class TEntity
{
 private $vars = array();
 private $changed = array();
 private $isNew = 1;
 public $entity_type = 'default';
// public $guid = 0;
 public function setData($data)
 {
 $this->isNew = 0;
 $this->vars = $data;
 }
 public function toJSON()
 {
 //foreach($this->vars 
 }
 public function save()
 {

    $entyman = new TDBEntities();
    $classname = 'TDB'.ucfirst(getplural($this->entity_type)); // cast the entity type to the model name
    if(class_exists( $classname ))
    {
        $db = new $classname();
        // remove hard fields from the soft fields
        $fields = $db->Describe();
        foreach ($fields as $field)
        {
        if (isset($this->changed[$field['Field']]))
        {
            $data[$field['Field']] = $this->changed[$field['Field']];
            unset($this->changed[$field['Field']]);
        }
        }
    }
    else
       $db = $entyman;   //if no model then use the generic entity model

  if ($this->isNew)
  {
   $this->guid = $entyman->createEntity($this->entity_type);  // create an entry in the entity table
   $data['guid'] = $this->guid;
     
   $id = $db->insert($data);          // save hard values to entity types table
   $this->isNew = 0;
   }
   else
     {
     if (count($data) > 0)
     $db->save($data);
     //$db->saveValues($this->changed);  // save soft values

     }
$db->updateVars($this->guid,$this->changed);  // save soft values

$this->vars =  $this->vars +  $this->changed + $data;
$this->changed = array();
 }


 
 public function __set($index, $value)
 {
      if ($this-> __get($index) <>  $value)
	$this->changed[$index] = $value;
 }

 public function __get($index)
 {
     if (isset($this->changed[$index]))
       return $this->changed[$index];
       
     return $this->vars[$index];
 }
 
function load()
 {
    $classname = 'TDB'.ucfirst(getplural($this->entity_type));
    if(class_exists( $classname ))
    {
        $db = new $classname();
        $data = $db->findFirst('guid ='.$this->guid);
        if (!empty($data))
         $this->vars = $this->vars + $data;
        if (!empty($this->vars))
         $this->vars = $data; 
    }
 }
 public function getConnetions($con_type = '')
 {
 $db = new TDBConnections();
  return $db->getConnections($this,$con_type);
  
 }
 public function makeConnection($connection, $connectTo,$direction = 'B')
 {
 pullGuid($connectTo) ;

 
  $db = new TDBConnections();
  
  $db->makeConnection($connection, $this->guid, $connectTo,$direction);
 }
}
class TDBEntities extends TDBBase
{
function nextEntity()
{
$tableData = array();
if (get_class($this)=='TDBEntities')
{
$entityData = $this->next();
 if ($entityData == false)
     return false;
$classname = 'TDB'.ucfirst(getplural($entityData['entity_type']));
    if(class_exists( $classname ))
    {
        $db = new $classname();
        $tableData = $db->findFirst('guid ='.$this->guid);
    }
}
else
{
 $tableData = $this->next();
 if ($tableData == false)
     return false;
 $dbEntities = new TDBEntities();
 $entityData = $dbEntities->findFirst('guid ='.$tableData['guid']);
}

$entity_type = 'TDB'.ucfirst($entityData['entity_type']);
if (class_exists($entity_type))
    $entity = new $entity_type();
  else
    $entity = new TEntity();

$vars = $this->loadvars($entityData['guid']);
$data =  $entityData + $vars + $tableData;
$entity->setData($data);

return $entity;

}

function getEntity($guid)
{
 $this->find('id = '.$guid);
 return $this->nextEntity();
}
function loadvars($guid)
{
 $sql = 'select `name`,`value` from entities_values where guid = '.$guid;
   $data = $this->query($sql,true);
 $result = array();
   foreach($data as $item)
   {
       $result[$item['name']] = $item['value'];
   }
 return $result;
}
function updateVars($guid ,$data)
{
    unset($data['guid']);
    foreach($data as $key => $item)
    {
        $sql = 'replace into entities_values  (`guid` ,`name` ,`value`) values ("'.$guid.'" ,  "'.$key.'" ,  "'.$item.'" )';
        $this->update($sql);
    }
}
function createEntity($entity_type)
{
return $this->update('insert into entities  set entity_type = "'.$entity_type.'"');    
}
function deleteEntity($guid)
{
     $this->update('delete from entities  where guid ='.$guid);

}
}

class TDBConnections extends TDBBase
{
function getConnections($entity,$con_type='',$direction = 'B')
{
pullGuid($entity);
$sql =  'select * from connections where  ( conFrom=  "'.$entity .'",conTo=  "'. $entity.'" )';
if (!empty($con_type))
  $sql .=  ' and  connection="'.$con_type.'" ' ;
  
  
}
function makeConnection($connection, $connectFrom, $connectTo,$direction ='B')
{
pullGuid($connectFrom);
pullGuid($connectTo);
    
$sql = 'replace into conections  set connection="'.$connection.'" , conFrom=  "'.$connectFrom .'",conTo=  "'. $connectTo.'" ';
$this->execute($sql);    
}
}

?>
