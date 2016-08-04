<?php

namespace app\Models;

use Egulias\EmailValidator\EmailValidator;
use \Gelembjuk\WebApp\Exceptions\FormException as FormException;

class Profile extends Login {
    
    public function updatePassword($curpassword,$password) 
    {
        $this->signinRequired();
        
        if (trim($password) == '' || trim($curpassword) == '') {
            throw new \Exception($this->_('enterpassword','account'));
        }
        
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserRecord($this->getUserID());
        
        if ($user_rec['logintype'] != 'site') {
            // update login type. it can be old user registered on MEP
            $logindb->updateUserLoginType($user_rec['id'],'site');
        }
        
        if (!$this->checkPassword($curpassword,$user_rec['password'])) {
            throw new \Exception($this->_('curpassworddoesntmatch','account'));
        }
        
        if ($user_rec['active'] == '0') {
            throw new \Exception($this->_('accountisnotactive','account'));
        }
        
        $logindb->updateUserPassword($user_rec['id'],$this->getPasswordHash($password));
        
        return true;
    }
    public function changeUserEmail($email) {
        $this->signinRequired();
        
        // check this email is used somewhere else
        $logindb = $this->application->getDBO('Login');
        
        // try to set new email address
        $validator = new EmailValidator;
    
        if (!$validator->isValid($email)) {
            throw new FormException('email',$this->_('entervalidemail','account'));
        }
        
        if ($logindb->checkEmailUsed($email)) {
            throw new FormException('email',$this->_('emailnameisused','account'));
        }
        
        // make confirm code
        $user_rec = $logindb->getUserRecord($this->getUserID());
        
        $confirmcode = $this->getAutologinCode($user_rec['id'],'', 0,$email);
        
        $messagingmodel = $this->application->getModel('Messaging');
        $messagingmodel->sendEmailChangeConfirmEmail($email,$user_rec,$confirmcode);
        
        return true;
    }
    public function changeUserEmailConfirm($email,$code) {
        $userid = $this->getUserFromAutologinCode($code,$email,10000);
        
        if ($userid < 1) {
            throw new \Exception($this->_('emailconfirmationfailedtryagain','account'));
        }
        
        $profiledb = $this->application->getDBO('Profile');
        // check if existent user
        
        $user_rec = $profiledb->getUserRecord($userid);
        
        if (!$user_rec) {
            throw new \Exception($this->_('usernotfound','account'));
        }
        
        $profiledb->updateUserEmail($userid,$email);
        
        return true;
    }
    
    public function updateAccountName($name) 
    {
		$this->signinRequired();
		
		if (trim($name) == '') {
			throw new \Exception($this->_('nameisempty','userpanel'));
		}
		
		$profiledb = $this->application->getDBO('Profile');
		
		$profiledb->updateUserName($this->getUserID(),$name);
		
		return true;
	}
}
