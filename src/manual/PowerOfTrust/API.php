<?php

/**
* PHP Api for PowerOfTrust
*/

namespace PowerOfTrust;

class API {
    protected $config = [];
    public function __construct($cofig = [])
    {
        $this->config = $config;
    }
    
    protected function executeAPICall()
    {
    }
    
    public function findPerson($name,$surname)
    {
        return null;
    }
    
    public function addToWatchList($uid)
    {
        return true;
    }
    
    public function removeFromWatchList($uid)
    {
        return true;
    }
    
    public function getStatusAndEvents($uid,$events = [])
    {
        return [];
    }
    
    public function clearStatusAndEvents($uid, $events = [])
    {
        return true;
    }
    
    public function checkPersonExists($personid)
    {
        return true;
    }
    
    public function getPersonNameByID($personid)
    {
        return $personid;
    }
}
 