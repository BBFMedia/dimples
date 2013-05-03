<?php
require_once(dirname(__FILE__).'/setup.php');
require_once(dirname(__FILE__).'/test_TDBBase.php');
require_once(dirname(__FILE__).'/test_TDBEntities.php');
require_once(dirname(__FILE__).'/test_TEntity.php');
class DimpleTest extends TestSuite {
 function __construct() {
        parent::__construct();
      //    $this->add(new TestTDBBase());
    }
}