<?php

/**
* PHP Api for PowerOfTrust
*/

namespace PowerOfTrust;

class API {
    protected $config = [];
    public function __construct($config = [])
    {    
        $this->config = $config;
    }
    
    protected function executeAPICall($method, $params = array())
    {
        if (trim($this->config['host']) == '') {
            throw new \Exception('Host not provided');
        }
        if (trim($this->config['port']) == '' || $this->config['port'] < 1) {
            throw new \Exception('Port not provided');
        }
        
        $socket = fsockopen ( $this->config['host'], $this->config['port'], $errno , $errstr , 5);
        
        if (!$socket) {
            throw new \Exception(sprintf('Can not open socket connection. %s',$errstr));
        }
        
        $data = array(
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id'=>'1'
        );
        
        $jsondata = json_encode($data,JSON_UNESCAPED_UNICODE);
        //echo $jsondata."\n";
        fwrite($socket, $jsondata."\n");
        
        $response = '';
        
        while (!feof($socket)) {
            $response .= fread($socket, 1);
            
            $parsed = @json_decode($response,true);
            
            if (is_array($parsed)) {
                break;
            }
        }
        fclose($socket);
        
        if (is_array($parsed)) {
            return $parsed['result'];
        }
        throw new \Exception('No correct JSON response');
        
    }
    
    public function testConnection()
    {
        try {
            $result = $this->executeAPICall('get_uid', array('name'=>"Юрій",'surname'=>"Бабак"));
            
            return ""; 
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }
    
    public function findPerson($name,$surname)
    {
        $result = $this->executeAPICall('get_uid', array('name'=>$name,'surname'=>$surname));
        
        if ($result['ok'] == '1') {
            return $result['uid'];
        }
        return null; 
    }
    
    public function addToWatchList($uid)
    {
        $result = $this->executeAPICall('add_to_watch_list', array('uid'=>$uid));
        
        if ($result['ok'] == true) {
            return true;
        }
        if ($result['msg'] == 'already watched') {
            return true;
        }
        throw new APIException($result['msg'],$result['msg']);
    }
    
    public function removeFromWatchList($uid)
    {
        $result = $this->executeAPICall('remove_from_watch_list', array('uid'=>$uid));
        
        if ($result['ok'] == true) {
            return true;
        }
        if ($result['msg'] == 'not watched') {
            return true;
        }
        throw new APIException($result['msg'],$result['msg']);
    }
    
    public function getStatusAndEvents($uid,$events = [])
    {
        $result = $this->executeAPICall('get_stats_and_events', array('uid'=>$uid, 'items' => $events));
        
        if ($result['ok'] == true) {
            $ret = [];
            
            foreach ($events as $eventtype) {
                if (isset($result[$eventtype])) {
                    $ret[$eventtype] = $result[$eventtype];
                }
            }
            
            return $ret;
        }
        throw new APIException($result['msg'],$result['msg']);
    }
    
    public function clearStatusAndEvents($uid, $events = [])
    {
        $result = $this->executeAPICall('clear_stats_and_events', 
            array('uid'=>$uid, 'all_for' => $events, 'events_related_to_me_ids' => []));
        print_r($result);
        return true;
    }
    
    public function getPersonNameByID($personid)
    {
        return $personid;
    }
}
 