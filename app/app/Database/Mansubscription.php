<?php
/**
 * User account read functions
 */
namespace app\Database;

class Mansubscription extends Subscription {
    public function saveSubsription($userid,$personid,$scheduler,$contents,$options,$personname = '')
    {
        // check if was already stored 
        $item = $this->findOne('subscription',
            ['userid' => (int) $userid,
            'personid' => $personid]
            );
        
        if ($item) {
            $this->Coll('subscription')->update(
                ['userid' => (int) $userid, 'personid' => $personid], 
                ['$set'=> 
                    ["scheduler" => $scheduler,
                    "contents" => $contents,
                    "options" => $options]
                ]
            );
            return true;
        }
        
        $insertdata = [
            'userid' => (int) $userid,
            'personid' => $personid,
            "scheduler" => $scheduler,
            "contents" => $contents,
            "options" => $options
            ]; 
            
        if ($personname != '') {
            $insertdata['personname'] = $personname;
        }
        
        $this->Coll('subscription')->insert($insertdata);
        return true;
    }
    
    public function removeSubscription($userid, $personid)
    {
        $this->Coll('subscription')->remove(
                ['userid' => (int) $userid, 'personid' => $personid],
                ["justOne" => true]
            );
        
        return true;
    }
}