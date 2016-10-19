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
		$this->logQ('Hourly notifications','cron');
		$subsmodel = $this->application->getModel('Notifications');
		
		$logs = $subsmodel->prepareHourlyEmails();
		
		return $logs;
	}
	
	public function daily() 
    {
	$this->logQ('Daily notifications','cron');
        $subsmodel = $this->application->getModel('Notifications');
		
		$logs = $subsmodel->prepareDailyEmails();
		
		return $logs;
    }
    
    public function sendPreparedEmails()
    {
	$this->logQ('Send queued emails','cron');
        $messagingmodel = $this->application->getModel('Messaging');
        
        $logs = $messagingmodel->sendPreparedSubsriptionEmails(50, 15);
        
        return $logs;
    }
}
