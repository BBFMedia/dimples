<?php
require_once('setup.php');

require_once('../TDBBase.php');

class TDBTestbase extends TDBBase

{
   public $_joins = array('testbase2' =>array('select'=> array('field2 as bob' ,'field1 as jim'),'table'=>'testbase2'
                        , 'on' => 'testbase2.testbase_id = testbase.id'));
 

}


class TestTDBBase extends UnitTestCase
{     
 
  public $creationdata  = '
  
   CREATE  TEMPORARY TABLE testbase ( 
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`field1` TEXT NOT NULL ,
`field2` INT NOT NULL
) ENGINE = MYISAM 
';
  public $creationdata2  = '
  
   CREATE  TEMPORARY TABLE testbase2 ( 
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`field1` TEXT NOT NULL ,
`field2` INT NOT NULL ,
 `testbase_id` INT NOT NULL 
) ENGINE = MYISAM 
';

     function testCreateDatabase() {
        
        $db = new TDBTestbase();
        
        $db->update($this->creationdata);
         $db->update($this->creationdata2);
       
        $db->update('insert into testbase ( field1 ) values ( "bacon" ) ');
        $db->update('insert into testbase ( field1 ) values ( "bacon2" ) ');
        $db->update('insert into testbase2 ( field1,testbase_id ) values ( "junk" ,2) ');
        $db->update('insert into testbase2 ( field1,testbase_id ) values ( "trash" ,3) ');
          $data = array( 'field1' =>'apples','field2' =>'4');
        $db->insert($data);
          $data = array( 'field1' =>'peach','field2' =>'9');
        $db->insert($data);
        
        $db->find(' field1 = "peach"');
        $rs = $db->next();
    
     //  $this->assertFalse(count($rs) == 1);
       $this->assertTrue($rs['field1'] == "peach");
       $rs['field2'] = 55;
       $sql = $db->createUpdate($rs , ' id = '.$rs['id']);
       
       $db->update($sql);
  //simple find     
        $db->find(' field1 = "peach"');
        $rs = $db->next();
       $this->assertTrue($rs['field2'] == 55);
       
  // count
  
       $c = $db->tableCount();
      $this->assertTrue($c == 4);
        
  //join
  ///need more complete join test. requires more tables    
        $db->join('testbase2');
        $rs = $db->findFirst('testbase.id = 2');
        
         $this->assertTrue($rs['jim'] == 'junk');
 
 //Key Labling
 
 
     $rs = $db->query('select field1, field2, id from  testbase',true,true);
     $this->assertTrue( $rs['bacon2']['field1'] == 'bacon2');
     $rs = $db->query('select field1, field2, id from  testbase',false,true);
     $this->assertTrue( $rs['apples']['1'] == '4');
     $rs = $db->query('select field1, field2, id from  testbase',false,false);
     $this->assertTrue( $rs['3']['0'] == 'peach');
     $rs = $db->query('select field1, field2, id from  testbase',true);
     $this->assertTrue( $rs['2']['id'] == '3');
     
        
         
//->escape
    $e = $db->escape('dfsdfsd"dfsdfsdfsf/sdfd\dfgdf/\ndfdg'."dd'dd");
    $this->assertTrue($e == 'dfsdfsd\"dfsdfsdfsf/sdfd\\\\dfgdf/\\\\ndfdg'."dd\'dd");
 
 //grouping
        $db->update('insert into testbase ( field1,field2 ) values ( "bacon",5 ) ');
        $db->update('insert into testbase ( field1,field2 ) values ( "bacon",7 ) ');
    
        $rs = $db->group('field1')->find()->fetchAll();
       $this->assertTrue(count($rs)==4);
//order
        $rs = $db->order('field1')->find()->fetchAll();
         
      $this->assertTrue($rs[2]['field1'] == 'bacon2' ); 
      $this->assertTrue($rs[1]['field1'] == 'bacon' ); 
        $rs = $db->order('field1','desc')->find()->fetchAll();
      $this->assertTrue($rs[1]['field1'] == 'bacon2' ); 
      $this->assertTrue($rs[2]['field1'] == 'bacon' ); 
//limit    
       $rs = $db->limit(1,2)->find()->fetchAll();
         $this->assertTrue(count($rs) == 2 ); 
      $this->assertTrue($rs[1]['field1'] == 'bacon' ); 
//paging with limit 

         $db->limit(0,2,true)->find();   //with paging
        $c = 0;
        while($row = $db->next())
          {
            $c++;
          }
       $this->assertTrue($c == 4 ); 
        $db->limit(0,2,false)->find();    //without paging
        $c = 0;
        while($row = $db->next())
          {
            $c++;
          }
       $this->assertTrue($c == 2 ); 
       $db->update('drop table testbase');
       $db->update('drop table testbase2');
         
    }
public function testPlurals()
   {
        
    $r = (( getplural('man') == 'men')  and
     (getplural('apple') == 'apples')  and
     (getplural('index') == 'indexes')  and
     (getplural('process') == 'processes')  and
     (getplural('diagnosis') == 'diagnoses')  and
     (getplural('datum') == 'data')  and
     (getplural('child') == 'children')  );
     $this->assertTrue($r ); 
       
    
   }
} 
 
 