<?php
/**
 * User account read functions
 */
namespace app\Database;

class Subscription extends Mongo {
    public function getPlatformSubscription($userid)
    {
        return $this->findOne('subscription',
            ['userid' => (int) $userid,
            'personid' => 'system']
            );
    }
    
    public function getPersonsSubscriptions($userid)
    {
        $items = $this->find('subscription',
            ['userid' => (int) $userid, 'personid' => ['$ne' => 'system']]
            );
        
        return $items;
    }
    
    public function getPersonSubscription($userid,$personid)
    {
        return $this->findOne('subscription',
            ['userid' => (int) $userid,
            'personid' => $personid]
            );
    }
    public function getAllPersonsSubscribedForSystem($scheduler) 
    {
		$items = $this->find('subscription',
            ['personid' => 'system', "scheduler" => $scheduler]
            );
            
		$result = [];
		
		foreach ($items as $o) {
			$result[] = ['userid' => $o->userid, 'format' => $o->contents, 'subscription' => $o->options];
		}
		
		return $result;
    }
}