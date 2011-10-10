<?php


/**
* Entity model class
* @package Dimples
*
*/
 

/********************       
* array('entity_type'=>"users",         
*      'search_field'=>"username",     
*      "title_field"=>"username",      
*      "public_fields"=>"username",    
*      "entity_name"=>"User",          
*      "entity_name_plural"=>"Users" );    
*                                      
*****************************/


class TDBEntities extends TDBBase
{

static  $entity_types = array();

public $entityjoin =  array('table'=> 'entities','on'=>'entities.guid = relations.guid_b');


static function getSchema($entity_type)
{
 return   self::$entity_types[$entity_type];
}
static function addSchema($schema)
{
  self::$entity_types[$schema['entity_type']] = $schema;
}

function search($entity_type,$search)
{
  $schema = $this->getSchema( $entity_type);
  
  $sql = 'select id , '.$schema['title_field'].'  from '.$entity_type.' 
      left join entities on entities.guid = '.$entity_type.'.id   
       where  '
          .$schema['search_field'].' like "%'.$this->escape($search).'%"';

  $data = $this->query($sql,true);
  foreach($data as $key => $item)
     {
      $data[$key]['search_text'] = $item[$schema['title_field']];
      $data[$key]['entity_type'] = $entity_type;
     }
  return $data;
  
 }   
    function saveEntity(&$data)
    
    {
    $owner = $data['owner']?$data['owner']:$data['owner_guid'];
    unset($data['guid']);
    unset($data['owner']);
    unset($data['owner_guid']);
    unset($data['owner_rights']);
    unset($data['entity_type']);
    
   if (!empty($data['id']))
    {
 
    $sql = $this->createUpdate($data,'id = '.$data['id'] )   ;

    }
    else
    {
    
    $data['id'] = $this->createEntity(strtolower($this->getTable()),$owner);
    $sql = $this->createinsert($data)   ;
     }

   

    $this->update($sql); 
   return $data['id'];    
 } 
 
static function getEntity($guid)
{
 $db = new TDBBase();

 if (!is_array($guid))
   {
     $rs = $db->query('select * from entities where guid = '.$guid,true);
     $guid = $rs[0];
    }
 
 
 $rs = $db->query('select * from '.$guid['entity_type'].' where id = '.$guid['guid'],true);
 
 return array_merge($rs[0],$guid);

}
 function createEntity($entity_type,$owner=0)
{
  
     
     
     $this->update('insert into entities  set owner_guid = '.$owner.' ,entity_type = "'.$entity_type.'"');
     return  $this->lastInsertId();  
}

function owner($guid)
{
  $db = new TDBBase();
 if ((is_array($guid))and (empty($guid['owner_guid'])))
   $guid = $guid['id'];


 if (!is_array($guid))
   {
     $rs = $db->query('select guid, owner_guid from entities where guid = '.$guid,true);
     $guid = $rs[0];
    }
 
 
 return $guid['owner_guid'];

 }
 }
 

