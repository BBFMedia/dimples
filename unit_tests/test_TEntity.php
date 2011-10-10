<?php


require_once('setup.php');

require_once('../TDBBase.php');
require_once('../TDBEntities.php');
require_once('../TEntity.php');
require_once('../TDBMetaData.php');

class TDBEnity2tests extends TDBEntities

{
  public $_table = 'testrecords';
function pullEntity($guid)
{
 $rs = $this->getEntity($guid);
 
   $rs = $this->createObject($rs);  
   
 return $rs;
}

/**
* create a TProject object bases on the $data array
* @param array data are of product
* @return TProduct
* 
*
*/
function createObject($data)
{
 $rs = new TTestrecord();
 $rs->setFieldData($data);
 $rs->loadMetaData();
 return $rs;
}
  

}

class TTestrecord extends TEntity
{
        public function save()
      {
      parent::save();
      }

 }

class TestTEntity extends UnitTestCase
{     
  public $entitySql = "CREATE TEMPORARY TABLE `entities` (
  `guid` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(20) CHARACTER SET latin1 NOT NULL,
  `owner_guid` int(11) NOT NULL DEFAULT '0',
  `owner_rights` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`),
  KEY `entity_type` (`entity_type`)
) ;
";

 public $metacreate = "
 CREATE  TEMPORARY TABLE  `meta_data` (
  `guid` int(11) NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `last_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_type` text COLLATE utf8_unicode_ci NOT NULL,
  `meta` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`guid`,`name`(100))
)";
  public $creationdata  = '
  
   CREATE  TEMPORARY TABLE testrecords ( 
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`field1` TEXT NOT NULL ,
`field2` INT NOT NULL
) ENGINE = MYISAM 
';
  public $creationdata2  = '
  
   CREATE  TEMPORARY TABLE test2records ( 
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`field1` TEXT NOT NULL ,
`field2` INT NOT NULL ,
 `testbase_id` INT NOT NULL 
) ENGINE = MYISAM 
';

     function testCreateDatabase() {
        
        $db = new TDBEnity2tests();
        
        //setup database
        $db->update($this->entitySql);
         $db->update($this->creationdata);
       $db->update($this->metacreate);
      $db->update('insert into entities ( guid,entity_type ) values ( 1,"site" ) ');
      /////
      
      
      
// create entities long hand       
      $guid =  $db->createEntity('testrecords',1);
        $db->update('insert into testrecords (id, field1 ) values ('.  $guid.' ,"bacon" ) ');
      $guid =  $db->createEntity('testrecords',1);
        $db->update('insert into testrecords ( id ,field1 ) values ('.  $guid.', "bacon2" ) ');
 
 ///     saveEntity    create entiry  with no id
 
           $data = array('field2' =>'4','owner_guid'=>1);
  $db->saveEntity($data);
           $data = array('field1' =>'jim','field2' =>'4','owner_guid'=>1);
   $db->saveEntity($data);
           $data = array('field1' =>'Alex','owner_guid'=>$guid);
  $guid2 =   $db->saveEntity($data);   //returns a guid
  
 //   getEntity
   $rs =  $db->pullEntity( $guid); 
   $this->assertEqual(  $rs->field1 ,'bacon2')     ;
   $this->assertEqual(  $rs->owner_guid ,1);
   
   $rs->field1 = 'changed';
   $rs->save();
  
  $rs->bobis_youuncle = "yes";
    $rs->save();
    unset($rs);
   $rs =  $db->pullEntity( $guid);
   $this->assertEqual(  $rs->bobis_youuncle , "yes");
   
   
 // $newentity = new TTestrecord();
 // $newentity->field1 = "ducks";
 // $newentity->save();        
     $db->update('drop table meta_data');
     $db->update('drop table entities');
      $db->update('drop table testrecords');
      
    }

} 
 
 