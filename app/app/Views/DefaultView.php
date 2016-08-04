<?php

namespace app\Views;

use \Gelembjuk\WebApp\Exceptions\ViewException as ViewException;

class DefaultView extends \Gelembjuk\WebApp\View
{
    protected $showextendedview = false;
	
    public function init() 
    {
        $this->erroronnotfoundview = true;
    }
    
    protected function view() {
        $this->htmltemplate = 'default';
        
        if ($this->application->getUserID() > 0) {
            $subscriptionmodel = $this->application->getModel('Subscription');
            
            $this->viewdata['status'] = $subscriptionmodel->getSummary();
            
            $this->htmltemplate = 'home';
        }
        return true;
    }
    
    protected function beforeDisplay() {
        // set some extra information like user, some cpecial links etc
        if ($this->responseformat == 'html') {
                       
            $this->viewdata['USERID'] = $this->application->getUserID();
            
            $this->viewdata['USER'] = $this->application->getUserRecord();
            
            $branding = $this->application->getConfig('branding');
            
            $this->viewdata['config'] = $branding;
            
            if (empty($this->viewdata['html_title'])) {
            	  $this->viewdata['html_title'] = $branding['sitename'];
            }
            
            $this->viewdata['DEVSITE'] = $this->application->getConfig('devsite');
            $this->viewdata['THISISLOCALVERSION'] = $this->application->getConfig('localversion');
            
            if ($this->viewdata['CURRENTPAGE'] == '' || 
                $this->viewdata['CURRENTPAGE'] == 'controller') {
                $this->viewdata['CURRENTPAGE'] = strtolower($this->getName());
            }
            $this->viewdata['CURRENTSUBPAGE'] = $this->getPageSubID();
            
            $this->viewdata['showextendedview'] = $this->showextendedview;
            
            $this->htmlouttemplate_force = 'default';
        }
        
        return true;
    }
    protected function viewError() {
        parent::viewError();
        $this->htmlouttemplate_disable = false;
        $this->htmlouttemplate_force = 'default';
        
        return true;
    }
    protected function displayJSON() {
        // we always need 200 response for JSON requests. even if there is error
        // JSON is used for ajax
        if (!empty($this->viewdata['errorcode'])) {
            $this->viewdata['errorcode'] = 200;
        }
        parent::displayJSON();
    }
    protected function gI($name,$type='string',$default='',$maxlength=0) {
        return $this->getInput($name,$type,$default,$maxlength);
    }
    protected function mU($controllername,$message='',$methodtype = '', $methodname = '',
            $objectid = '', $objecttitle = '',$extra1 = '', $extra2 = '') {
        return $this->application->makeUrlQ($controllername,$message,$methodtype, $methodname,
            $objectid,$objecttitle,$extra1,$extra2);
    }
    /**
    * Redirect to home page if a user is already in
    */
    protected function goToHomeIfIn() 
    {
        if ($this->application->getUserID() > 0) {
            throw new ViewException($this->application->makeUrl('def'),
                'You are already in','',400,'redirect');
        }
    }
    /*
    * This is used to mark correct menu item for opened page
    */
    protected function getPageSubID()
    {
        return '';
    }
}