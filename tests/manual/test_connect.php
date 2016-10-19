<?php

$application = require_once('../../app/init.inc.php');

$config = ['host' => '172.31.19.42','port' => 7733];
$api = new \PowerOfTrust\API($config);

$result = $api->testConnection();

echo $result."\n";
