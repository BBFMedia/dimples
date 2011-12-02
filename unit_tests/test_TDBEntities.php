<?php
require_once('setup.php');

require_once(dirname(dirname(__FILE__)).'/TDBBase.php');
require_once(dirname(dirname(__FILE__)).'/TDBEntities.php');

class TDBEnitytest extends TDBEntities

{
  public $_table = 'testrecords';

   public $_joins = array('testbase2' =>array('select'=> array('field2 as bob' ,'field1 as jim'),'table'=>'testbase2'
                        , 'on' => 'testbase2.testbase_id = testbase.id'));
 

}


class TestTDBEntites extends UnitTestCase
{     
  public $entitySql = "CREATE TEMPORARY TABLE `entities` (
  `guid` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(20) CHARACTER SET latin1 NOT NULL,
  `owner_guid` int(11) NOT NULL DEFAULT '0',
  `owner_rights` tinyint(1) NOT NULL DEFAULT '1',
  namespace   varchar(20),
  PRIMARY KEY (`guid`),
  KEY `entity_type` (`entity_type`)
) ;
";
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
        
        $db = new TDBEnitytest();
        
        $db->update($this->entitySql);
         $db->update($this->creationdata);
      //   $db->update($this->creationdata2);
      $db->update('insert into entities ( guid,entity_type ) values ( 1,"site" ) ');
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
   $rs =  $db->getEntity( $guid); 
   $this->assertEqual(  $rs['field1'] ,'bacon2')     ;
   $this->assertEqual(  $rs['owner_guid'] ,1);
   
   $rs['field1'] = 'changed';
   
 // update date entity   saveEntity     with id
   $db->saveEntity($rs);
   $rs =  $db->getEntity( $guid); 
   $this->assertEqual(  $rs['field1'] ,'changed')     ;
   $this->assertEqual(  $rs['owner_guid'] ,1);
 //try owner function with a guid
   $owner = $db->owner($guid2);
   $this->assertEqual(  $owner ,$guid);
   
//owner function with a loadeed array that already has $owner    
   $test['owner_guid']  = 7;
   $owner = $db->owner($test);
   $this->assertEqual(  $owner ,7);
   
   unset($rs['owner_guid']);
   
   $owner = $db->owner($rs);
   $this->assertEqual(  $owner ,1);


     $db->update('drop table entities');
      $db->update('drop table testrecords');
      
    }

} 
 
 