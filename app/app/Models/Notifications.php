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
    
    protected function prepareEmails($timeinterval,$scheduler)
    {
		//$timeinterval = new \PowerOfTrust\Period(1451374688, 1458382307);
		
		$notifications = [];
        
        $potapi = $this->application->getPOTAPI();
        
        $subsdb = $this->application->getDBO('Subscription');
        // get all users subscribed for system on hourly basis
        $subsforsystem = $subsdb->getAllUsersSubscribedForSystem($scheduler);
        
        // if anyone subscribed for a system
        if (count($subsforsystem) > 0) {
			$activity = [];
			
			$activity['chatsay'] = $potapi->getChatSays($timeinterval);
			$activity['taskcreate'] = $potapi->getNewTasks($timeinterval);
			$activity['persons'] = $potapi->getNewPersons($timeinterval);
			$activity['taskcomments'] = $potapi->getNewTasksComments($timeinterval);
			
			$activity = $this->cleanActivitiesInfo($activity);
			
			foreach ($subsforsystem as $rec) {
				$events = $this->getSubsActivities($activity,$rec['subscription'],$rec['format']);
				
				if (count($events) == 0) {
					continue;
				}
				
				if (empty($notifications[$rec['userid']])) {
					$notifications[$rec['userid']] = [];
				}
				
				$notifications[$rec['userid']]['system'] = 
					['events' => $events, 'format' => $rec['format'],'persontitle' => $this->_('platform','subs')];
			}
        }
        
        $subsforsystem = $subsdb->getAllUsersSubscribedForPersons($scheduler);
        
        $useractionscache = [];
        
        foreach ($subsforsystem as $userid => $data) {
			foreach ($data as $personid => $info) {
				if (empty($useractionscache[$personid])) {
					// load activity of this person for a period
					$activity = [];
			
					$activity['chatsay'] = $potapi->getChatSays($timeinterval,$personid);
					$activity['taskcreate'] = $potapi->getNewTasks($timeinterval,$personid);
					$activity['taskcomment'] = $potapi->getNewTasksComments($timeinterval,$personid);
					
					$activity = $this->cleanActivitiesInfo($activity);
					
					$useractionscache[$personid] = $activity;
				} else {
					$activity = $useractionscache[$personid];
				}
				
				$events = $this->getSubsActivities($activity,$info['subscription'],$info['format']);
				
				if (count($events) == 0) {
					continue;
				}
				
				if (empty($notifications[$userid])) {
					$notifications[$userid] = [];
				}
				
				$notifications[$userid][$personid] = 
					['events' => $events, 'format' => $info['format'],'persontitle' => $potapi->getPersonNameById($personid)];
			}
        }
        
        $total = count($notifications);
        
        foreach ($notifications as $userid => $info) {
			$this->prepareNotification($userid,$info,$scheduler);
        }
        
        return ['Prepared '.$total.' emails'];
    }
    protected function cleanActivitiesInfo($activity)
    {
		$keys = array_keys($activity);
		
		foreach ($keys as $key) {
			if (count($activity[$key]) == 0) {
				unset($activity[$key]);
			}
		}
		return $activity;
    }
    protected function getSubsActivities($activity,$subs,$format)
    {
		$events = [];
				
		foreach ($subs as $item) {
			if (isset($activity[$item])) {
				if ($format == 'short') {
					$events[$item.'count'] = count($activity[$item]);
				} else {
					$events[$item] = $activity[$item];
				}
			}
		}
		return $events;
    }
    /**
    * @param $info array Is a hash of a POT person and related events array
    */
    protected function prepareNotification($userid,$info,$scheduler)
    {
        // there are 2 templates for email contents 
        // email template is defined by $scheduler
       
        // build email and save to DB to send it later
        $messagingmodel = $this->application->getModel('Messaging');
        
        $email = $messagingmodel->makeNotificationEmail($userid,$info,$scheduler);
        
        if ($email) {
			// save to a DB
			$notdb = $this->application->getDBO('Notification');
			$notdb->addNotification($email);
        }
        
        return true;
    }

}
