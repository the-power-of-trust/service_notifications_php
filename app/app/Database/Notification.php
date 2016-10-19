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
		$item = $this->Coll('emailqueue')->findOne();
		
		$rec = [];
		
		foreach ($item as $k => $v) {
			if (is_object($v)) {
				$nv = [];
				
				foreach ($v as $kk => $vv) {
					$nv[$kk] = $vv;
				}
				$v = $nv;
			}
			$rec[$k] = $v;
		}
		
		return $rec;
    }
    
    public function removeProcessed($_id)
    {
		$this->Coll('emailqueue')->deleteOne(['_id' => $this->makeObjectId($_id)]);
    }
}