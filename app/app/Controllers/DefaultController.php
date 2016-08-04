<?php

namespace app\Controllers;

use \Gelembjuk\WebApp\Exceptions\DoException as DoException;

class DefaultController extends \Gelembjuk\WebApp\Controller {
 
    protected function getRouter() 
    {
        return $this->application->getRouter('DefaultRouter');
    }
    
    protected function getViewer($name = '') 
    {
        if ($this->getName() == 'DefaultController') {
            return $this->application->getView('DefaultView',$this->router,$this);
        }
        return parent::getViewer(); // presume all other controllers will have views with same names
    }
    
    protected function getDefaultURI($message = null) 
    {
        $opts = array();
        
        if ($message !== null) {
            $opts = array('message'=>$message);
        }
        return $this->router->makeUrl($opts);
    }
    
    protected function getErrorURI($message)
    {
        return $this->router->makeUrl(array('error'=>$message));
    }
    
    protected function completeUrlOpts($opts) 
    {
        return $opts;
    }
    
    public function makeUrlQ($message='',$methodtype = '', $methodname = '',
            $objectid = '', $objecttitle = '',$extra1 = '', $extra2 = '') 
    {
        
        return $this->application->makeUrlQ($this->getName(),$message,$methodtype, $methodname,
            $objectid, $objecttitle,$extra1, $extra2);
    }
    
    protected function gI($name,$type='string',$default='',$maxlength=0) {
        return $this->getInput($name,$type,$default,$maxlength);
    }
    protected function initAuthSession() 
    {
        $authtype = $this->router->getAuthType();

        $this->router->initSession();
        $this->debug($this->router->getInput('remembermecode'));
        if ($_SESSION['userid'] == 0 && $this->router->getInput('remembermecode') != '') {
            $loginmod = $this->application->getModel('Login',array());
            $loginmod->logInWithCode($this->router->getInput('remembermecode'));
        }
        
        if ($_SESSION['userid'] > 0) {
            $this->application->setUserID($_SESSION['userid']);
        }
    }
    
    public function makeUrl($opts = array()) {
        $opts = $this->completeUrlOpts($opts);
        
        return parent::makeUrl($opts);
    }
    
    public function signinNotRequired() {
        $this->signinreqired = false;
    }
}
