<?php

$application = require_once('../../app/init.inc.php');

$config = ['host' => '10.0.3.15','port' => 7733];
$api = new \PowerOfTrust\API($config);

$result = $api->testConnection();

echo $result."\n";