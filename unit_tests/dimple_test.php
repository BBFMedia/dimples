<?php
require_once('setup.php');
require_once('test_TDBBase.php');
require_once('test_TDBEntities.php');
require_once('test_TEntity.php');
class DimpleTest extends TestSuite {
 function __construct() {
        parent::__construct();
      //    $this->add(new TestTDBBase());
    }
}