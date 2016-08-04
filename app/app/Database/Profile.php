<?php
/**
 * User account update functions
 */
 
namespace app\Database;

class Profile extends User {
    
    public function setSubscription($userid,$subscription) 
    {
        $this->Coll('users')->update(
            ['id' => (int) $userid], 
            ['$set'=> ["subscription" => ($subscription)?'y':'n']]
        );
        return true;
    }
    
    public function updateUserEmail($userid,$email) 
    {
        $this->Coll('users')->update(
            ['id' => (int) $userid], 
            ['$set'=> ["email" => $email]]
        );
        return true;
    }
    
    public function updateUserName($userid,$name) 
    {
        $this->Coll('users')->update(
            ['id' => (int) $userid], 
            ['$set'=> ["name" => $name]]
        );
        return true;
    }
}