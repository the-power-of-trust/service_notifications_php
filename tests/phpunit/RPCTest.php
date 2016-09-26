<?php
use PHPUnit\Framework\TestCase;

class RPCTest extends TestCase
{
    // ...

    public function testConnection()
    {
        $config = ['url' => 'http://10.0.3.15:7733'];
        $api = new \PowerOfTrust\API($config);
        
        $result = $api->testConnection();
        
        $this->assertEquals("",$result);
    }

    // ...
}