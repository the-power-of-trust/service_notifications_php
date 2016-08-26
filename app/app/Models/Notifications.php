<?php

namespace app\Models;

class Notifications extends Subscription {
    public function prepareHourlyEmails()
    {
        // get all daily subscriptions 
        
        // check what of them had changes
    }
    
    public function prepareDailyEmails()
    {
        
    }
    
    protected function prepareNotification($userid,$format,$events,$scheduler)
    {
        // there are 4 templates for email contents 
        // email template is defined by $format + $scheduler
    }
}
