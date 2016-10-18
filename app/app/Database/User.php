<?php
/**
 * User account read functions
 */
namespace app\Database;

class User extends Mongo {
    public function getRowById($id) 
    {
        $item = $this->Coll('users')->findOne(['id' => (int)$id]);
        
        return $this->cleanRecord($item);
    }
    
    public function getUser($id) 
    {
        return $this->getRowById($id);
    }
    
    public function getUserEmptyRec() 
    {
        return array(
            'id' => '0',
            'name' => '',
            'email' => '',
            'password' => '',
            'created' => '',
            'active' => '0',
            
            );
    }
    
    public function getUserRecord($id_or_record) 
    {
        if (is_array($id_or_record) || is_object($id_or_record)) {
            return $id_or_record;
        }
        return $this->getUser($id_or_record);
    }
    
    public function getUserByEmail($email) 
    {
        $item = $this->Coll('users')->findOne(['email' => $email]);
        
        return $this->cleanRecord($item);
    }
}