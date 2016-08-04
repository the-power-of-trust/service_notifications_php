<?php

namespace app\Controllers;

use \Gelembjuk\WebApp\Exceptions\DoException as DoException;

class Subscription extends DefaultController {
    public function init() 
    {
        $this->signinreqired = true;
        $this->defmodel = $this->application->getModel('Subscription');
    }
    
    protected function doSavesubscription() 
    {
        $personid = $this->gI('id','plainline');
        $scheduler = $this->gI('scheduler','alpha');
        $contents = $this->gI('contents','alpha');
        $options = $this->gI('options','array');
        $this->debug($personid);
        try {
            $this->defmodel->saveSubsription($personid,$scheduler,$contents,$options);
            
            $this->router->setMessageToSession($this->_('subscriptionupdated','subs'),'success');
        } catch(\Exception $e) {
            $this->router->setMessageToSession($e->getMessage(),'e');
            
            throw new DoException(
                $this->makeUrl(array('view'=>'editsystem')),
                $e->getMessage(),'savesubscription',400,'redirect');
        }
            
        return true;
    }
    
    protected function doDelete() 
    {
        $personid = $this->gI('personid','plainline');
        
        $this->defmodel->deleteSubsription($personid);
            
        $this->router->setMessageToSession($this->_('success'),'success');
            
        return true;
    }
}
