<?php

namespace app\Models;

//use Guzzle\Http\Client;
// php cron.php "int_api_key=internalapikey&do=hourly"

class Cron extends \Gelembjuk\WebApp\Model {
	public function test() {
		$this->debug('test cron in model');
		
	}
	public function hourly() 
	{
		$subsmodel = $this->application->getModel('Notifications');
		
		$logs = $subsmodel->prepareHourlyEmails();
		
		return $logs;
	}
	
	public function daily() 
    {
        $subsmodel = $this->application->getModel('Notifications');
		
		$logs = $subsmodel->prepareDailyEmails();
		
		return $logs;
    }
    
    public function sendPreparedEmails()
    {
        $messagingmodel = $this->application->getModel('Messaging');
        
        $logs = $messagingmodel->sendPreparedSubsriptionEmails(50, 15);
        
        return $logs;
    }
}
