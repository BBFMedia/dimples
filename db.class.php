<?php

class db_debug {
      public $queries = array();
     function queryCount()
        {
        return count($this->queries);
        }
      function query($sql)
        {
          return TDBBase::query($sql,true);
         }  
            }
class  DBException extends PDOException{};    

class db{

/*** Declare instance ***/
private static $instance = NULL;
private static $lazy_instance = NULL;
private static $db_debug = null;
 static function get_db_debug()
  {

          if (empty(self::$db_debug))
    {
     self::$db_debug = new db_debug();
    }
    return self::$db_debug;
     }
 static	function getMicroTime() {
		$time = microtime();
		$time = explode(' ', $time);
		return $time[1] + $time[0];
	}
	
static function addQuery($sql,$start)
{
   
   $query = array(
        'sql' => $sql,
        'time' => (self::getMicroTime() - $start)*1000
    );
    array_push(self::get_db_debug()->queries, $query); 
}


/**
*
* the constructor is set to private so
* so nobody can create a new instance using new
*
*/
private function __construct() {
  /*** maybe set the db name here later ***/
}

/**
*
* Return DB instance or create intitial connection
*
* @return object (PDO)
*
* @access public
*
*/
public static function getInstance() {


///***  uri for the PDO connection  ***/

$hosturi = DIMPLE_DB_URI; // for mysql
// $hosturi = 'sqlite:'.$hostname ; // for sqlite
///***  username ***/
$username = DIMPLE_DB_USERNAME;

///***  password ***/
$password = DIMPLE_DB_PASSWORD;



if (!self::$instance)
    {
    self::$instance = new PDO($hosturi, $username, $password);;
    self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
return self::$instance;
}

public static function getLazy_Instance() {

 if (!USE_LAZY_DB )
   return self::getInstance();
///***  uri for the PDO connection  ***/

$hosturi = DIMPLE_DB_URI_LAZY; // for mysql
// $hosturi = 'sqlite:'.$hostname ; // for sqlite
///***  username ***/
$username = DIMPLE_DB_USERNAME_LAZY;

///***  password ***/
$password = DIMPLE_DB_PASSWORD_LAZY;



if (!self::$instance)
    {
    self::$lazy_instance = new PDO($hosturi, $username, $password);;
    self::$lazy_instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
return self::$instance;
}

    /*
     * Passes on any static calls to this class onto the singleton PDO instance
     * @param $chrMethod, $arrArguments
     * @return $mix
     */
final public static function __callStatic( $chrMethod, $arrArguments ) {
           
        if (preg_match('/^lazy_(.*)/',$chrMethod,$match) )
         {
           $chrMethod = $match[1];
        $objInstance = self::getLazyInstance();
        }
        else
           $objInstance = self::getInstance();
     
        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);
       
    }
/**
*
* Like the constructor, we make __clone private
* so nobody can clone the instance
*
*/
private function __clone(){
}
static function startTransaction()
{
 self::getInstance()->beginTransaction();
}
static function commit()
{
 self::getInstance()->commit();
}
static function rollback()
{
 self::getInstance()->rollback();
}

} /*** end of class ***/


?>
