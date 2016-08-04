<?php

namespace app\Database;

class Login extends User {

    public function checkEmailUsed($email)
    {
        return $this->findOne("users",['email' => $email]);
    }
    
    public function addUser($logintype,$active,$name,$email,$passwordhash, $externalid = '')
    {
        $active = ($active === true || $active === '1' || $active === 1 || $active === 'y' || $active === 'yes')?'1':'0';
        
        if ($logintype == 'site') {
            $externalid = '';
        }
        
        $userdocument = [
            'id' => (int) $this->getNextID("users"),
            'name' => $name,
            'email' => $email,
            'password' => $passwordhash,
            'created' => date(),
            'active' => $active,
            'logintype' => $logintype,
            'externalid' => $externalid,
            'subscription' => 'n'
        ];
       
        $this->logQ($userdocument,'userdb|registration');
        
        $this->Coll('users')->insert($userdocument);
        
        return $userdocument['id'];
    }
    
    protected function changeActiveStatus($userid,$active) 
    {
        $active = ($active === true || $active === '1' || $active === 1 || $active === 'y' || $active === 'yes')?'1':'0';
        
        $this->Coll('users')->update(
            ['id' => (int) $userid], 
            ['$set'=> ["active"=>$active]]
        );
        
        return true;
    }
    
    public function activateUser($userid) 
    {
        return $this->changeActiveStatus($userid,true);
    }
    
    public function deActivateUser($userid) 
    {
        return $this->changeActiveStatus($userid,false);
    }
    
    public function updateUserPassword($userid,$passwordhash) 
    {
        $this->Coll('users')->update(
            ['id' => (int) $userid], 
            ['$set'=> ["password" => $passwordhash]]
        );
        return true;
    }
    
    public function updateUserLoginType($userid,$type) 
    {
        $this->Coll('users')->update(
            ['id' => (int) $userid], 
            ['$set'=> ["logintype" => $type]]
        );
        return true;
    }
    
    public function getUserByExternalAuth($network,$externalid) 
    {
        return $this->findOne('users',
            ['externalid' => $externalid,
            'logintype' => $network]);
    }
}