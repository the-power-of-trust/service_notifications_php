<?php

/**
* PHP Api for PowerOfTrust. Exceptions
*/

namespace PowerOfTrust;

class APIException extends \Exception {
    protected $textcode;
    
    public function __construct($message = '' , $textcode = '', $number = 0) {
        parent::__construct($message,$number);
        $this->textcode = $textcode;
    }
    public function getTextCode() {
        return $this->textcode;
    }
}
 