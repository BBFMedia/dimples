<?php


class TDBRelations extends TDBBase {
  public $_table = ' relations';
    public $_joins = array(
                 'entities' => array('table'=> 'entities','on'=>'entities.guid = relations.guid_b','select' => array('entity_type')) ,
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




function getRelations($guid, $relationship,$direction = '1t2')
{

$this->join('entities');

if ($direction = '1t2')
 $this->find('guid_a = '.$guid.' and  relationship = "'.$relationship.'" ');
/*if ($direction = '2t1')
 $this->find('guid2 = '.$guid.' and  relationship = "'.$relationship.'" ');
if ($direction = 'bi')
 $this->find(('guid2 = '.$guid.' or  guid2 = '.$guid.') and  relationship = "'.$relationship.'" ');
  */
 
}

function next()
{
 $data = parent::next();
 
 if (empty($data))
    return null;
 
 
 $result = $this->getEntity($data['guid_b']);
 
 
 return array_merge($result,$data);
 
 
 
 
}
static function getEntity($guid)
{

 $db = new TDBBase();
 $rs = $db->query('select * from entities where guid = '.$guid,true);
 $result = $rs[0];
 
 
 $rs = $db->query('select * from '.$result['entity_type'].' where id = '.$guid,true);
 
 return array_merge($rs[0],$result);

}
}