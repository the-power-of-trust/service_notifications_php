<?php

/**
* PHP Api for PowerOfTrust using direct DB access
*/

namespace PowerOfTrust;



class APIDB extends API{
    protected $connection = null;
    protected $connectioncreatetime = 0;
    protected $connectiontimelimit = 0;
    
    protected $namescache = [];
    
    protected function getConnection()
    {
        if ($this->connection !== NULL) {
            if ($this->connectiontimelimit > 0 && 
                $this->connectioncreatetime > 0 && 
                time() - $this->connectioncreatetime > $this->connectiontimelimit) {
                $this->closeConnection();
            } else {
                return $this->connection;
            }
        }

        $connstart = microtime(true);
        
        $att=0;
        
        $connstring = "mongodb://".$this->config['dbhost'];
        
        $mongolient = null;
        
        do {
            // hide errors. 
            $mongolient = new \MongoDB\Client($connstring);
            $att++;

            if (!$mongolient && $att<4) {
                sleep(1);   // try again in 1 sec
            }

        } while (!$mongolient && $att<4);
        
        if (!$mongolient) {
            throw new APIException('Can not connect to the POT DB server','connection',1);
        }
        
        // this is database object
        $this->connection = $mongolient->{$this->config['dbname']};
        
        if (!$this->connection) {
            throw new APIException('Can not connect to the DB server: ','connection',1);
        }
        
        $conntime = microtime(true) - $connstart;
        
        $this->connectioncreatetime = time();

        return $this->connection;
    }

    protected function closeConnection() {
        if ($this->connection != NULL) {
            $this->connection=NULL;     
            $this->connectioncreatetime = 0;
            return TRUE;
        }
        return FALSE;
    }
    
    protected function getColl($collection)
    {
        $conn = $this->getConnection();
        
        if (trim($collection) == '') {
            throw new APIException("Collection name not provided");
        }
        
        return $conn->selectCollection($collection);
    }
    
    public function getPersonNameById($uid)
    {
        if (!empty($this->namescache[$uid])) {
            return $this->namescache[$uid];
        }
        
        $coll = $this->getColl('Persons');
        
        $person = $coll->findOne(['_id' => $uid]);
        
        if ($person) {
            $this->namescache[$uid] = $person->name.' '.$person->surname;
            return $this->namescache[$uid];
        }
        
        return '';
    }
    
    public function testConnection()
    {
        try {
            $this->getConnection();
            
            $person = $this->findPerson("Юрій","Бабак");
            
            if (!$person) {
                return "Test person not found";
            }
            
            return ""; 
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    
    
    public function findPerson($name,$surname)
    {
        $coll = $this->getColl('Persons');
        
        $person = $coll->findOne(['name' => $name,'surname' => $surname]);
        
        if ($person) {
            return $person->_id;
        }
        
        return null;
    }
    
    /**
    * Chat says
    */
    public function getChatSays(Period $period)
    {
        $coll = $this->getColl('PublicLog');
        
        $list = $coll->find(['data._action' => 'chat_say', 'data._at' => ['$gte' => $period->getFromTime(), '$lte' => $period->getToTime()]]);
        
        $result = [];
        
        foreach($list->toArray() as $o) {
                $result[] = ['text' => $o->data->msg, 'time' => date('r', $o->data->_at), 'user' => $this->getPersonNameById($o->data->_uid)];
        }
        return $result;
    }
    public function getChatSaysCount(Period $period)
    {
        $list = $this->getChatSays($period);
        
        return count($list);
    }
    
    /**
    * Persons
    */
    public function getNewPersons(Period $period)
    {
    }
    public function getNewPersonsCount(Period $period)
    {
    }
}
 