<?php

namespace app\Models;

//use Guzzle\Http\Client;

class Cron extends \Gelembjuk\WebApp\Model {
	public function test() {
		$this->debug('test cron in model');
		
	}
	public function hourly() 
	{
		$logs=array();
		
		return $logs;
	}
	
	public function daily() 
    {
        $logs=array();
        
        return $logs;
    }
}
