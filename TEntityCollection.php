<?php
/**
 * Created by PhpStorm.
 * User: Adrian
 * Date: Mar 9, 2011
 * Time: 4:13:04 PM
 * To change this template use File | Settings | File Templates.
 */

class TEntityCollection extends TEntity
{
  public $entity_type = 'EntiryCollection';
  public $modelname ='';
  public $_items  = null;
  function ItemCount()
  {
      return count($this->_items);
  }
  function Items($index)
  {
      return $this->_items[$index];
  }
function loadItems()
{
    $this->_items = array();
     if ($this->guid > 9)

    {
       if (!empty($this->modelname ))
             $col = new $this->modelname();
           else
            $col = new TDBEntities(); /// this will actually not work because of collection_guid may need a join
       $col->find('collection_guid = '.$this->guid );
       while ($item =$col->nextEntity())
       {
         $this->_items[] = $item;
       }
    }
}
 function AddEntity($entity)
{
 if (!is_array($this->_items))
 {
     $this->loadItems();
 }
  $this->_items[] = $entity;

}

function save()
{
    parent::save();
   foreach($this->_items  as $item)
   {
       $item->collection_guid = $this->guid;
      
       $item->save();
   }
}

}