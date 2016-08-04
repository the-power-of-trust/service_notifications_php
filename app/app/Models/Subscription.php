<?php

namespace app\Models;

class Subscription extends \Gelembjuk\WebApp\Model {
    /*
    * get subscription summary for this user
    */
    public function getSummary()
    {
        $data = [];
        
        $subsdb = $this->application->getDBO('Subscription');
        
        // subscription for system messages
        $data['platform'] = ['enabled' => false, 'settings' => []];
        
        $platform = $subsdb->getPlatformSubscription($this->getUserID());
        
        if ($platform) {
            $data['platform']['enabled'] = true;
            $data['platform']['settings'] = $platform;
        }
        
        $data['persons'] = $subsdb->getPersonsSubscriptions($this->getUserID());
        
        return $data;
    }
    
    public function getEventsStructure()
    {
        $structure = [];
        
        $structure['platform'] = \PowerOfTrust\Description::getPlatformEvents();
        $structure['person'] = \PowerOfTrust\Description::getPersonEvents();
        
        return $structure;
    }
    
    public function getPersonSettings($personid)
    {
        $subsdb = $this->application->getDBO('Subscription');
        
        $personsettings = $subsdb->getPersonSubscription($this->getUserID(),$personid);
        
        if (!$personsettings) {
            throw new \Exception($this->_('personnotfound','subs'));
        }
        
        return $personsettings;
    }
    
    public function findPerson($name,$surname)
    {
        $potapi = $this->application->getPOTAPI();
        
        return $potapi->findPerson($name,$surname); 
    }
    
    public function saveSubsription($personid,$scheduler,$contents,$options)
    {
        $subsdb = $this->application->getDBO('Mansubscription');
        
        // this will work only for persons
        $personname = '';
        
        if (trim($personid) == '') {
            $personid = 'system';
        }
        
        if ($scheduler != 'instant' && $scheduler != 'daily') {
            throw new \Exception($this->_('unknownscheduler','subs'));
        }
        
        if ($contents != 'short' && $contents != 'extended') {
            throw new \Exception($this->_('unknowncontents','subs'));
        }
        
        if (count($options) == 0) {
            throw new \Exception($this->_('noeventsselected','subs'));
        }
        
        if ($personid != 'system') {
            // check if this person exists
            $potapi = $this->application->getPOTAPI();
            
            $personname = $potapi->getPersonNameByID($personid);
            
            if (!$personname) {
                throw new \Exception($this->_('personnotfound','subs'));
            }
        }
        
        $subsdb->saveSubsription($this->getUserID(),$personid,$scheduler,$contents,$options,$personname);
        
        return true;
    }
    public function deleteSubsription($personid)
    {
        $subsdb = $this->application->getDBO('Mansubscription');
        
        $personsettings = $subsdb->getPersonSubscription($this->getUserID(),$personid);
        
        if (!$personsettings) {
            throw new \Exception($this->_('personnotfound','subs'));
        }
        
        $subsdb->removeSubscription($this->getUserID(),$personid);
        
        return true;
    }
}
