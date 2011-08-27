<?php

class TDBMetaData extends TDBBase
{
 public $_table = 'meta_data';
function getMetaData($guid)
{

 $this->find('guid = '.$guid);
 while ($data = $this->next() )
   {
    $rs[$data['name']] = $data['value'];
   }
   
   return $rs;
}
function saveMetaData($guid,$data)
{
// guid , name ,value , last_changed
 
 foreach($data as $key => $item)
 {
 $this->update('replace into '.$this->_table.' set name = "'.$this->escape($key).'" , guid = '.$this->escape($guid)
            .' , value = "'.$this->escape($item).'" , last_changed = '.time());
            }   
}
}
