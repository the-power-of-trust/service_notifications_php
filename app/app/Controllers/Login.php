<?php

namespace app\Controllers;

use \Gelembjuk\WebApp\Exceptions\DoException as DoException;
use \Gelembjuk\WebApp\Exceptions\FormException as FormException;

class Login extends DefaultController {
    public function init() 
    {
        $this->defmodel = $this->application->getModel('Login');
    }
    
    protected function doLogin() 
    {
        $email = $this->getInput('email','plainline');
        $password = $this->getInput('password','plainline');
        $rememberme = $this->getInput('rememberme','alphaext');
        
        try {
            $referrer = $this->router->getReferrer();
            $redirecturl = $this->defmodel->logIn($email,$password,$rememberme,$referrer);
            
            $this->viewdata['redirecturl'] = $redirecturl;
            $this->logQ('redir url '.$this->viewdata['redirecturl']);
            return array('success',$redirecturl);
        } catch (\Exception $exception) {
            throw new DoException(
                $this->makeUrl(array('message'=>'e:'.$exception->getMessage(),'email'=>$email)),$exception->getMessage(),'login',400,'redirect');
        }
        
        return array('success',$this->makeUrlQ(array('message'=>'s:'.$this->_('success'))));
    }
    
    protected function doSociallogin() 
    {
        $network = $this->getInput('id','alpha');
        
        try {
            $referrer = $this->router->getReferrer();
            $url = $this->defmodel->getSocialLoginStartUrl($network,$referrer);
            
            $this->viewdata['url'] = $url;
            
            // redirect is not forced as some tool can request for url to redirect later
            return array('success',$url);
        } catch (\Exception $exception) {
            throw new DoException(
                $this->makeUrl(array('message'=>'e:'.$exception->getMessage())),$exception->getMessage(),'login',403,'redirect');
        }
        
        return true;
    }
    
    protected function doSociallogincomplete() 
    {
        $network = $this->getInput('id','alpha');
        
        try {
            // get extra arguments for this network
            // auth systems can not read inputs directly
            $inputs = $this->defmodel->getSocialLoginExtraInputs($network);
            
            $extraoptions = array();
            
            foreach ($inputs as $arg) {
                $extraoptions[$arg] = $this->getInput($arg);
            }
             
            $redirecturl = $this->defmodel->loginWithSocial($network,$extraoptions);
            
            $this->viewdata['url'] = $redirecturl;
            
            // redirect is not forced as some tool can request for url to redirect later
            return array('success',$redirecturl);
        } catch (\Exception $exception) {
            throw new DoException(
                $this->makeUrl(array('message'=>'e:'.$exception->getMessage())),$exception->getMessage(),'login',403,'redirect');
        }
    }
    
    protected function doLogout() 
    {
        $authtype = $this->router->getAuthType();
        
        // session is inited in this controller
        $this->defmodel->logOut();

        return array('success',$this->makeUrl(array('s'=>'home')));;
    }
    
    protected function doRegister() 
    {
        $name = $this->getInput('name','plainline');
        $email = $this->getInput('email','plainline');
        $password = $this->getInput('password','plainline');
        
        $subscribe = $this->getInput('subscribe','bool');
        
        $antispam = $this->getInput('antispam','array'); // this is not used yet
        
        try {
            $userid = $this->defmodel->registerUser($name,$email,$password,$subscribe);
            // success. activation is needed
            
            return array('success',$this->makeUrl(array('view'=>'registered','email'=>$email)));
        } catch (\Exception $exception) {
            
            if ($exception instanceof FormException) {
                $this->addViewerData('input',$exception->getInput());
            }
            
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException($this->makeUrlQ('e:'.$exception->getMessage(),'view','registration'),$exception->getMessage(),400);
        }
        
        return true;
    }
    
    protected function doActivate() 
    {    
        $code = $this->getInput('id','plainline');
        $email = $this->getInput('email','plainline');
        
        try {
            $url = $this->defmodel->activateUser($code,$email);
            // success. activation is needed
            
            $this->addViewerData('continueurl',$url);
            
            return array('success',$this->makeUrlQ('','view','activated'));
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException($this->makeUrlQ('e:'.$exception->getMessage()),$exception->getMessage(),400);
        }
        
        return true;
    }
    
    protected function doActivationresend() 
    {
        
        $email = $this->getInput('email','plainline');
        $newemail = $this->getInput('newemail','plainline');
        
        try {
            $sentemailaddr = $this->defmodel->resendUserActivation($email,$newemail,true);
            // success. activation is needed
            
            return array('success',
                $this->makeUrl(array('view'=>'registered','email'=>$sentemailaddr,'message'=>'s:'.$this->_('activationwasresent','account',$sentemailaddr))));
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException(
                $this->makeUrl(array('view'=>'registered','email'=>$email,'message'=>'e:'.$exception->getMessage())),
                $exception->getMessage(),'resend',400,'redirect'/*always redirect, not diplay error page without redirect*/);
        }
        
        return true;
    }
    
    protected function doSendpasswordreset() 
    {
        $email = $this->getInput('email','plainline');
        
        try {
            $this->defmodel->sendPasswordResetEmail($email);
            
            return array('success',
                $this->makeUrl(array('view'=>'forgot','message'=>'s:'.$this->_('resetpasswordemailwassent','account'))));
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException(
                $this->makeUrl(array('view'=>'forgot','message'=>'e:'.$exception->getMessage())),
                $exception->getMessage(),'reset',400,'redirect');
        }
    }
    
    protected function doResetpassword() 
    {
        $code = $this->getInput('code','plainline');
        $email = $this->getInput('email','plainline');
        $password = $this->getInput('password','plainline');
        $password_c = $this->getInput('password_confirmation','plainline');
        
        if ($password != $password_c) {
            $text = $this->_('passwordandconfirmationaredifferent','account');
            
            throw new DoException(    
                $this->makeUrl(array('view'=>'forgot','id'=>$code,'email'=>$email,
                    'message'=>'e:'.$text)), $text,'reset',400,'redirect');
        }
        
        try {
            $this->defmodel->resetPassword($code,$email,$password);
            
            return array('success',
                $this->makeUrl(array('view'=>'forgot','message'=>'s:'.$this->_('passwordwasreset','account'))));
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException(
                $this->makeUrl(array('view'=>'forgot','message'=>'e:'.$exception->getMessage())),
                $exception->getMessage(),'reset',400,'redirect');
        }
    }
    
    /**
    * THis is used for Social login integration when email not possible to get from social network 
    */
    protected function doSetaccemail() 
    {
        $this->signinRequired();
        
        $email = $this->getInput('email','plaintext');
        
        try {
            $url = $this->defmodel->setUserEmail($email);
            
            if ($url == '') {
                $url = $this->makeUrl(array('s'=>'home'));
            }
            
            return array('success',$url);
        } catch (\Exception $exception) {
            // model will rteurn Exception, but we need extended exception with a redirect url
            throw new DoException(
                $this->application->makeUrl(array('s'=>'home')),
                    $exception->getMessage(),'type',400,'redirect');
        }
    }
}
