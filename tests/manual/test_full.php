<?php

$application = require_once('../../app/init.inc.php');

$config = ['host' => '10.0.3.15','port' => 7733];
$api = new \PowerOfTrust\API($config);

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

// add to watch list not existent
try {
    $result = $api->addToWatchList('Обама_Барак');
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}

if ($result) {
    echo "FAIL: Add to watch list. Not Existent\n";
} else {
    echo "Success: Add to watch list. Not Existent. Error is $err\n";
}

// existent
try {
    $result = $api->addToWatchList('Бабак_Юрій');
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}
    
if (!$result) {
    echo "FAIL: Add to watch list. Existent. $err\n";
} else {
    echo "Success: Add to watch list. Existent\n";
}


// existent repeated to atch when already watched
try {
    $result = $api->addToWatchList('Бабак_Юрій');
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}
    
if (!$result) {
    echo "FAIL: Repeated Add to watch list. Existent. $err\n";
} else {
    echo "Success: Repeated Add to watch list. Existent\n";
}


// get status and events
// not existent
try {
    $result = $api->getStatusAndEvents('Обама_Барак',['events_related_to_me']);
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}

if ($result) {
    echo "FAIL: Get Status And Events. Not Existent. Returned ".implode(',',array_keys($result))."\n";
} else {
    echo "Success: Get Status And Events. Not Existent. Error is $err\n";
}

// get status and events
// existent
try {
    $result = $api->getStatusAndEvents('Бабак_Юрій',['events_related_to_me']);
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}

if (!$result) {
    echo "FAIL: Get Status And Events. Existent. Error is $err\n";
} else {
    echo "Success: Get Status And Events. Existent. Returned ".implode(',',array_keys($result))."\n";
}

// =================== REMOVE ==============================================

// remove from watch list non existent 
try {
    $result = $api->removeFromWatchList('Обама_Барак');
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}

if ($result) {
    echo "FAIL: Remove from watch list. Not Existent\n";
} else {
    echo "Success: Remove from watch list. Not Existent. Error is $err\n";
}

// remove from watch list existent 
try {
    $result = $api->removeFromWatchList('Бабак_Юрій');
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}

if ($result) {
    echo "Success: Remove from watch list. Existent\n";
} else {
    echo "Fail: Remove from watch list. Existent. Error is $err\n";
}


// repeated remove from watch list existent 
try {
    $result = $api->removeFromWatchList('Бабак_Юрій');
} catch (Exception $e) {
    $err = $e->getMessage();
    $result = null;
}

if ($result) {
    echo "Success: Repeated Remove from watch list. Existent\n";
} else {
    echo "Fail: Repeated Remove from watch list. Existent. Error is $err\n";
}