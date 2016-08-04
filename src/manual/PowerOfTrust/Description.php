<?php

/**
* Description of the PowerOfTrust structure
*/

namespace PowerOfTrust;

class Description {
    public static function getPlatformEvents()
    {
        return [
            'chatsay' => 'Chat message posted by any person',
            'taskcreate' => 'New task created',
        ];
    }
    
    public static function getPersonEvents()
    {
        return [
            'chatsay' => 'Chat message posted',
            'taskcreate' => 'Task created by a person',
            'taskcomment' => 'Comment added to a task',
            'taskcommentresponse' => 'Response on a task comment',
        ];
    }
}