<?php

/**
* PHP Api for PowerOfTrust. Mookup
* This class overwrites API class and operates with some fake data
*/

namespace PowerOfTrust;

class APIMook extends API{
    protected $fakepersons = [
        'Роман Гелемб’юк',
        'Юрій Бабак',
        'Олексій Аяхов'
    ];
    protected $fakestatus = [
        'Роман Гелемб’юк' => [],
        'Юрій Бабак' => [],
        'Олексій Аяхов' => []
    ];
    public function findPerson($name,$surname)
    {
        if (in_array($name.' '.$surname,$this->fakepersons)) {
            return $name.' '.$surname;
        }
        
        return null;
    }
    
    public function addToWatchList($uid)
    {
        if (in_array($uid,$this->fakepersons)) {
                return true;
        }
        throw new APIException("Person not found",'notfound');
    }
    
    public function removeFromWatchList($uid)
    {
        if (in_array($uid,$this->fakepersons)) {
                return true;
        }
        throw new APIException("Person not found",'notfound');
    }
    
    public function getStatusAndEvents($uid,$events = [])
    {
        return [];
    }
    
    public function clearStatusAndEvents($uid, $events = [])
    {
        return true;
    }
}
 