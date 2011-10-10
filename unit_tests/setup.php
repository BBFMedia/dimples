<?php

define('_SIMPLETEST_PATH_' ,'../../simpletest');
require_once(_SIMPLETEST_PATH_.'/autorun.php');

 error_reporting(E_ALL  & ~E_NOTICE & ~E_WARNING);

//define('DIMPLE_DB_URI', "sqlite::memory:");
//define('DIMPLE_DB_USERNAME','');
//define('DIMPLE_DB_PASSWORD','');

define('DIMPLE_DB_URI', "mysql:host=127.0.0.1;dbname=test");
define('DIMPLE_DB_USERNAME','root');
define('DIMPLE_DB_PASSWORD','');