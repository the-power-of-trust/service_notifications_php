<?php

/**
* PHP Api for PowerOfTrust
*/

namespace PowerOfTrust;

class Period {
    protected $fromtime = 0;
    protected $totime = 0;
    
    public function __construct($fromtime = 0, $totime = 0)
    {
        $this->fromtime = $fromtime;
        $this->totime = $totime;
    }
    
    public function getFromTime()
    {
        return $this->fromtime;
    }
    public function getToTime()
    {
        return $this->totime;
    }
}