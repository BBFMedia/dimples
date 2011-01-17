<?php

class db{

/*** Declare instance ***/
private static $instance = NULL;

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
    /*
     * Passes on any static calls to this class onto the singleton PDO instance
     * @param $chrMethod, $arrArguments
     * @return $mix
     */
final public static function __callStatic( $chrMethod, $arrArguments ) {
           
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

} /*** end of class ***/


?>
