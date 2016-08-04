<?php

namespace app\Controllers;

use \Gelembjuk\WebApp\Exceptions\DoException as DoException;
use \Gelembjuk\WebApp\Exceptions\FormException as FormException;

class Profile extends DefaultController {
    public function init() 
    {
        $this->signinreqired = true;
        $this->defmodel = $this->application->getModel('Profile');
    }
    
    protected function doUpdatepassword() 
    {
        $curpassword = $this->getInput('curpassword','plainline');
        $password = $this->getInput('password','plainline');
        
        try {
            $this->defmodel->updatePassword($curpassword,$password);
            
            return array('success',
                $this->application->makeUrl('Profile',array('message'=>'s:'.$this->_('passwordupdated','account'))));
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException(
                $this->application->makeUrl('Profile',array('message'=>'e:'.$exception->getMessage())),
                $exception->getMessage(),'password',400,'redirect');
        }
    }
    
    protected function doChangeaccemail() 
    {
        $this->signinRequired();
        
        $email = $this->getInput('email','plaintext');
        
        try {
            $url = $this->defmodel->changeUserEmail($email);
            
            if ($url == '') {
                $url = $this->makeUrl(array('s'=>'home'));
            }
            
            return array('success',$url);
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException(
                $this->application->makeUrlQ('profile',$exception->getMessage()),
                    $exception->getMessage(),'type',400,'redirect');
        }
    }
    
    protected function doChangeaccemailconfirm() 
    {
        
        $email = $this->getInput('email','plaintext');
        $code = $this->getInput('id','plaintext');
        
        try {
            $this->defmodel->changeUserEmailConfirm($email,$code);
            
            return array('success',$this->makeUrl(array('s'=>'home','message'=>'Success')));
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException(
                $this->application->makeUrlQ('profile',$exception->getMessage()),
                    $exception->getMessage(),'type',400,'redirect');
        }
    }
    
    protected function doChangeaccname() 
    {
		
		$name = $this->getInput('name','plaintext');
		
		try {
			$this->defmodel->updateAccountName($name);
			
			return array('success',$this->makeUrlQ());
		} catch (\Exception $exception) {
			// model will rteurn Exception, but we need extended exception with a redirect url
			throw new DoException(
				$this->makeUrlQ('e:'.$exception->getMessage()),
					$exception->getMessage(),'name',400,'redirect');
		}
	}
}
