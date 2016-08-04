<?php

namespace app\Views;

class Subscription extends DefaultView{
    protected function beforeDisplay() {
        if ($this->responseformat == 'html') {
            $this->viewdata['structure'] = $this->controller->getDefModel()->getEventsStructure();
        }
        parent::beforeDisplay();
    }
    protected function view() 
    {
        $this->htmltemplate = 'manage';
        
        $this->viewdata['status'] = $this->controller->getDefModel()->getSummary();
        
        return true;
    }
    
    protected function viewEditsystem() 
    {
        $this->htmltemplate = 'editsystem';
        
        $this->viewdata['status'] = $this->controller->getDefModel()->getSummary()['platform'];
        
        return true;
    }
    
    protected function viewEditperson() 
    {
        $personid = $this->gI('personid','plainline');
        
        $this->htmltemplate = 'editperson';
        
        $this->viewdata['personstatus'] = $this->controller->getDefModel()->getPersonSettings($personid);
        
        return true;
    }
    
    protected function viewAddperson() 
    {
        $this->htmltemplate = 'addperson';
        
        return true;
    }
    
    protected function viewFind() 
    {
        $name = $this->gI('name','plainline');
        $surname = $this->gI('surname','plainline');
        
        $this->viewdata['personid'] = $this->controller->getDefModel()->findPerson($name,$surname);
        $this->viewdata['personfullname'] = $name.' '.$surname;
        
        $this->viewdata['found'] = (!empty($this->viewdata['personid']))?'y':'n';
        return true;
    }
}