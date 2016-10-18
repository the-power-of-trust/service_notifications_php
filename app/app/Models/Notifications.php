<?php

namespace app\Models;

class Notifications extends Subscription {
    public function prepareHourlyEmails()
    {
        // get all hourly subscriptions 
        
		$timeinterval = new \PowerOfTrust\Period(time() - 3600, time());
		
		return $this->prepareEmails($timeinterval,'instant');
    }
    
    public function prepareDailyEmails()
    {
        // get all daily subscriptions 
        
		$timeinterval = new \PowerOfTrust\Period(time() - 3600 * 24, time());
		
		return $this->prepareEmails($timeinterval,'daily');
    }
    
    protected function prepareEmails($period,$scheduler)
    {
		$notifications = [];
        
        $potapi = $this->application->getPOTAPI();
        
        $subsdb = $this->application->getDBO('Subscription');
        // get all users subscribed for system on hourly basis
        $subsforsystem = $subsdb->getAllPersonsSubscribedForSystem($scheduler) ;
        
        // if anyone subscribed for a system
        if (count($subsforsystem) > 0) {
			$lasthoursystem = [];
			
			$lasthoursystem['chatsays'] = $potapi->getChatSays($timeinterval);
			
			foreach ($subsforsystem as $rec) {
				if (empty($notifications[$rec['userid']])) {
					$notifications[$rec['userid']] = [];
				}
				$notifications[$rec['userid']]['system'] = [];
			}
        }
        
        // check what of them had changes
    }
    
    protected function prepareNotification($userid,$format,$events,$scheduler,$subscription)
    {
        // there are 4 templates for email contents 
        // email template is defined by $format + $scheduler
    }
}
