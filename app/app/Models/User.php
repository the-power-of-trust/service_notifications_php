<?php

namespace app\Models;

class User extends \Gelembjuk\WebApp\Model {
    public function getUserRecord($user_rec = 0) {
        if ($user_rec === 0) {
            $user_rec = $this->getUserID();
        }
        $userdb = $this->application->getDBO('User');
        
        $user_rec = $userdb->getUserRecord($user_rec);
        
        if (!$user_rec) {
            $user_rec = array('id' => 0);
        }
        return $user_rec;
    }
}
