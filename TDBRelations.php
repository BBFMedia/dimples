<?php

/**
*  relations model class
* @package Dimples
*
*/

class TDBRelations extends TDBEntities {
  public $_table = ' relations';
    public $_joins = array(
                 'entities' => array('table'=> 'entities','on'=>'entities.guid = relations.guid_b') ,
                 'revEntities' => array('table'=> 'entities','on'=>'entities.guid = relations.guid_a') ,
                        );
function addRelation($guid1 , $guid2 , $relationship)
{
$this->update('replace into relations set  guid_a = '.$guid1.' , guid_b = '.$guid2.' ,
        relationship = "'.$relationship.'"');
}

function removeRelation($guid1 , $guid2 , $relationship)
{
$this->update('delete from relations where  guid_a = '.$guid1.' and guid_b = '.$guid2.' and
        relationship = "'.$relationship.'"');
}

function removeAllRelations($guid1 , $guid2 )
{
 $this->update('delete from relations where  guid_a = '.$guid1.' and guid_b = '.$guid2.' ');
}


function getRelations($guid, $relationship,$entity_type = null)
{

$this->join('entities');

 $sql = ' guid_a = '.$guid.' and  relationship = "'.$relationship.'" ';
 
if  (!empty($entity_type)) 
   $sql .= ' and entities.entity_type = "'.$entity_type.'" ';
  
  $this->find($sql);
}
function getRevRelations($guid, $relationship,$entity_type = null)
{

$this->join('revEntities');

$sql = ' guid_b = '.$guid.' and  relationship = "'.$relationship.'" ';                     
 
if  (!empty($entity_type)) 
   $sql .= ' and entities.entity_type = "'.$entity_type.'" ';
 
 
  
  $this->find($sql);
 
}
function next()
{
 $data = parent::next();                                        

 if (empty($data))
    return null;
 
 
 $result = $this->getEntity($data);
 
 
 return array_merge($result,$data);
 
 
 
 
}
 function deleteEntity($guid)
 {
  db::startTransaction();
  $this->update('delete from relations where guid = '.$this->escape($guid));
  parent::deleteEntity($guid);
  db::commit();
   
 
 }
}