<?php


require_once "TDBBase.php";

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

$entity_type = 'T'.ucfirst($entityData['entity_type']);
if (can_load($entity_type))
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
function createEntity($entity_type,$owner=0)
{
    pullGuid($owner);
     $this->update('insert into entities  set owner_guid = '.$owner.' ,entity_type = "'.$entity_type.'"');
return  $this->lastInsertId();  
}
function deleteEntity(&$entity)
{
        pullGuid($entity);
     $this->update('delete from entities  where guid ='.$entity);


}
}

class TDBConnections extends TDBEntities
{
function getConnections($entity,$con_type='',$direction = 'B')
{
pullGuid($entity);
switch($direction){
    case 'B':
$sql =  'select * from connections where  ( conFrom=  "'.$entity .'" or conTo=  "'. $entity.'" )';
  break;
    case 'F':
$sql =  'select * from connections where  ( conFrom=  "'.$entity .'" )';
  break;
    case 'T':
$sql =  'select * from connections where  ( conTo=  "'.$entity .'" )';
  break;
}

if (!empty($con_type))
  $sql .=  ' and  connection="'.$con_type.'" ' ;
  
$this->join('entity')->find($sql);



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
