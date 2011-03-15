<?php
/**
 * Created by PhpStorm.
 * User: Adrian
 * Date: Mar 9, 2011
 * Time: 3:59:58 PM
 * To change this template use File | Settings | File Templates.
 */
require_once 'TDBBase.php';
 
function pullGuid(&$entity)
{
 if (!is_numeric($entity))
    {
     $entity = $entity->guid;
    }
   return $entity;
}
class TEntity
{
 private $vars = array();
 private $changed = array();
 private $isNew = 1;
 public $entity_type = 'default';
// public $guid = 0;
 public function setData($data)
 {
 $this->isNew = 0;
 $this->vars = $data;
 }
 public function getView($viewtype = '')
 {
     return $this->entity_type.ucfirst($viewtype).'View';
 }
 public function save()
 {
    if (count($this->changed) == 0)
     return;


    $classname = 'TDB'.ucfirst(getplural($this->entity_type)); // cast the entity type to the model name
    if(class_exists( $classname ))
    {
        $db = new $classname();
        $entyman = $db;
        // remove hard fields from the soft fields
        $fields = $db->Describe();
        foreach ($fields as $field)
        {
        if (isset($this->changed[$field['Field']]))
        {
            $data[$field['Field']] = $this->changed[$field['Field']];
            unset($this->changed[$field['Field']]);
        }
        }
    }
    else
    {
       $db = null;   //if no model then use the generic entity model
       $entyman = new TDBEntities();
       $data = array();
    }
  if ($this->isNew)
  {
   $this->guid = $entyman->createEntity($this->entity_type,$this->owner);  // create an entry in the entity table
if( isset($db))
{
   $data['guid'] = $this->guid;

   $id = $db->insert($data);          // save hard values to entity types table
  }
   $this->isNew = 0;
   }
   else
     {
    if(( isset($db)) and  (count($data) > 0))
     $db->save($data,'guid = '.$this->guid);
     //$db->saveValues($this->changed);  // save soft values

     }
$entyman->updateVars($this->guid,$this->changed);  // save soft values

$this->vars =  $this->vars +  $this->changed + $data;
$this->changed = array();
 }



 public function __set($index, $value)
 {
      if ($this-> __get($index) <>  $value)
	$this->changed[$index] = $value;
 }

 public function __get($index)
 {
     if (isset($this->changed[$index]))
       return $this->changed[$index];

     return $this->vars[$index];
 }
 function load()
 {
    $classname = 'TDB'.ucfirst(getplural($this->entity_type));
    if(class_exists( $classname ))
    {
        $db = new $classname();
        $data = $db->findFirst('guid ='.$this->guid);
        if (!empty($data))
         $this->vars = $this->vars + $data;
        if (!empty($this->vars))
         $this->vars = $data;
    }
 }
 public function getConnetions($con_type = '')
 {
 $db = new TDBConnections();
  return $db->getConnections($this,$con_type);

 }
 public function makeConnection($connection, $connectTo,$direction = 'B')
 {
 pullGuid($connectTo) ;


  $db = new TDBConnections();

  $db->makeConnection($connection, $this->guid, $connectTo,$direction);
 }
}