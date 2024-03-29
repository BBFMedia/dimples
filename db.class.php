<?php
/**
* db_bug
* a helper class for debuging.
* Pleas note the circular reference of TDBBase
* keeps a list of querys made to database in development
*/

class db_debug {
      public $queries = array();
     /**
      * db_debug::queryCount()
      *
      * @return integer the number of queries made
      */
     function queryCount()
        {
        return count($this->queries);
        }
      /**
       * db_debug::query()
       * a dirty way to get sql results while debuging. never use in production
       *
       * @param mixed $sql
       * @return mixed
       */
      function query($sql)
        {
          return TDBBase::query($sql,true);
         }
            }


class  DBException extends PDOException{
};

class db{

/*** An array of pdo database connections ***/

private static $instances = array();
private static $transactionCount = 0;
private static $db_debug = null;

/**
 * db::get_db_debug()
 * helper function to access db_debug class
 * @return db_debug
 */
static function get_db_debug()
  {

          if (empty(self::$db_debug))
    {
     self::$db_debug = new db_debug();
    }
    return self::$db_debug;
     }
/**
 * db::getMicroTime()
 * get the mirco time as integer
 * @return integer
 */
static	function getMicroTime() {
		$time = microtime();
		$time = explode(' ', $time);
		return $time[1] + $time[0];
	}

/**
 * db::addQuery()
 *
 * adds a query to the db_debug class and figure time.
 * also sends to firephp if installed
 * @param mixed $sql
 * @param mixed $start  the microtime the sql started.
 *
 */
static function addQuery($sql,$start)
{

   $query = array(
        'sql' => $sql,
        'time' => (self::getMicroTime() - $start)*1000
    );
    if (function_exists('fb') )
     fb($sql, 'SQL', FirePHP::LOG);
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
public static function getInstance($instname=null) {

if (!$instname)
  $instname = 'default';
// check is exists if not create it with defualt settings
if (!self::$instances[$instname])
    {
     self::createInstance($instname ,DIMPLE_DB_URI, DIMPLE_DB_USERNAME, DIMPLE_DB_PASSWORD);
    }

return self::$instances[$instname];
}
public static function createInstance($instname ,$hosturi, $username, $password)
{
 $instance = new PDO($hosturi, $username, $password,array(
    PDO::ATTR_PERSISTENT => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
 $instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 self::$instances[$instname] =  $instance;

}



    /*
     * Passes on any static calls to this class onto the singleton PDO instance
     * @param $chrMethod, $arrArguments
     * @return $mix
     */
final public static function __callStatic( $chrMethod, $arrArguments ) {

        if (preg_match('/^(.*)_(.*)/',$chrMethod,$match) )
         {
         $instance =  $match[2];
           $chrMethod = $match[2];
        }
        else
           $instance =  null;

        $objInstance = self::getInstance($instance);

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
static function startTransaction($instance = null)
{
 if (self::$transactionCount == 0)
 {
 self::getInstance($instance)->beginTransaction();
 }
 self::$transactionCount++;
}
static function commit($instance = null)
{
 self::$transactionCount--;
 if (empty(  self::$transactionCount))
 self::getInstance($instance)->commit();
}
static function rollback($instance = null)
{
 self::getInstance($instance)->rollback();
}

static function reset()
{
  self::$instances = null;  
}

static function setInstance($object, $instance="default")
{
  self::$instances[$instance] = $object;  
}

} /*** end of class ***/


