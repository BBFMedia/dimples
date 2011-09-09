<?php

class TDBMetaData extends TDBBase
{
 public $_table = 'meta_data';
function getMetaData($guid)
{

 $this->find('guid = '.$guid);
 while ($data = $this->next() )
   {
  
    $rs[$data['name']] = $data;
   }
  
   return $rs;
}
function saveMetaData($guid,$data)
{
// guid , name ,value , last_changed
 
 foreach($data as $key => $item)
 {

 $type = '';
  $meta = '';
  $data_type = '';
  $value = '';
// if (!empty($item['meta']))
//   $meta = ' meta = "'.$this->escape($item['meta']).'" , ';
    
 if (!empty($item['data_type']))
   $data_type = ' data_type = "'.$this->escape(strtolower($item['data_type'])).'" , ';
 if (isset($item['value']))
   $value = ' value = "'.$this->escape($item['value']).'" , ';
 $sql = 'replace into '.$this->_table.' set '.$data_type. $meta. $value
         .' name = "'.$this->escape($key).'" , guid = '.$this->escape($guid)
           .' , last_changed = now()';
           
          
           $this->update($sql);
 }   
}
}
