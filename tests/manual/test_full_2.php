<?php

$application = require_once('../../app/init.inc.php');

$config = ['dbhost' => 'localhost:27117','dbname' => 'power_of_trust'];
$api = new \PowerOfTrust\APIDB($config);

$result = $api->testConnection();

if ($result != '') {
    echo "Connect failed with error $result\n";
    exit;
}

echo "Success: Connection test\n";

// find person 
// not existent
$person_id = $api->findPerson("Барак","Обама");

if (!$person_id) {
    echo "Success: Find person. Unexistent\n";
} else {
    echo "FAIL: Find person: Unexistent: $person_id \n";
}

// existent
$person_id = $api->findPerson("Юрій","Бабак");

if (!$person_id) {
    echo "FAIL: Find person. Existent\n";
} else {
    echo "Success: Find person: Existent: $person_id \n";
}

$testperiod = new \PowerOfTrust\Period(1451374688, 1458382307);

$expectedcount = 12;

$chatsays = $api->getChatSays($testperiod);

if (count($chatsays) == $expectedcount) {
    echo "Success: Chat says for period \n";
} else {
    echo "FAIL: Chat says for period. Got ".count($chatsays)." records \n";
}

$chatsayscount = $api->getChatSaysCount($testperiod);

if ($chatsayscount == $expectedcount) {
    echo "Success: Chat says count for period \n";
} else {
    echo "FAIL: Chat says count for period. Got ".$chatsayscount." records \n";
}
