<?php

$application = require_once('../../app/init.inc.php');

$config = ['host' => '192.168.1.41','port' => 7733];
$api = new \PowerOfTrust\API($config);

$result = $api->testConnection();

echo $result."\n";
