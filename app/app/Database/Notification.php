<?php
/**
 * User account read functions
 */
namespace app\Database;

class Notification extends Mongo {
    public function addNotification($emailinfo)
    {
        $this->Coll('emailqueue')->insertOne($emailinfo);
        
        return true;
    }
    /**
    * Get one notification from a queue
    */
    public function getPreparedNotification()
    {
		return $this->Coll('emailqueue')->findOne();
    }
    
    public function removeProcessed($_id)
    {
		$this->Coll('emailqueue')->deleteOne(['_id' => $_id]);
    }
}