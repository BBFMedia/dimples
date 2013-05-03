<?php


require_once "TDBBase.php";

class TDBAttributes extends TDBEntities
{
static function getEntity($guid)
{   

          


$entityData = parent::getEntity($guid);




$vars = $this->loadvars($entityData['guid']);
$entityData =  $entityData + $vars ;


$entity->setData($data);

return $entity;

}

function loadvars($guid)
{
 $sql = 'select `name`,`value` from entity_values where guid = '.$guid;
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
    $toupdate = $data;
    $fields = $db->Describe($data['entity_type']);
    var_dump(  $fields );
    var_dump(  $data );
die;      
  foreach ($fields as $field)
        {
      
            unset($data[$field['Field']]);
        
        }
 
    foreach($data as $key => $item)
    {
    
        $sql = 'replace into entities_values  (`guid` ,`name` ,`value`) values ("'.$guid.'" ,  "'.$key.'" ,  "'.$item.'" )';
        $this->update($sql);
    }
}

function deleteEntity(&$entity)
{
     pullGuid($entity);
     $this->update('delete from entities  where guid ='.$entity);


}
}

