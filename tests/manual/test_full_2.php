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
$testuser = "Бабак_Юрій";

/**
* ==================================================================================================
* Chat
*/
$expectedcount = 12;
$expectedcountuser = 7;

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

$chatsays = $api->getChatSays($testperiod,$testuser);

if (count($chatsays) == $expectedcountuser) {
    echo "Success: Chat says for period for user\n";
} else {
    echo "FAIL: Chat says for period for user. Got ".count($chatsays)." records \n";
}

$chatsayscount = $api->getChatSaysCount($testperiod,$testuser);

if ($chatsayscount == $expectedcountuser) {
    echo "Success: Chat says count for period  for user\n";
} else {
    echo "FAIL: Chat says count for period for user. Got ".$chatsayscount." records \n";
}

/**
* ==================================================================================================
* Persons
*/
$expectedcount = 10;

$persons = $api->getNewPersons($testperiod);

if (count($persons) == $expectedcount) {
    echo "Success: Persons \n";
} else {
    echo "FAIL: Persons. Got ".count($persons)." records \n";
}

$personscount = $api->getNewPersonsCount($testperiod);

if ($personscount == $expectedcount) {
    echo "Success: Persons count\n";
} else {
    echo "FAIL: Persons count. Got ".$personscount." records \n";
}

/**
* ==================================================================================================
* Tasks
*/
$expectedcount = 21;
$expectedcountuser = 17;

$tasks = $api->getNewTasks($testperiod);

if (count($tasks) == $expectedcount) {
    echo "Success: Tasks \n";
} else {
    echo "FAIL: Tasks. Got ".count($tasks)." records \n";
}

$taskscount = $api->getNewTasksCount($testperiod);

if ($taskscount == $expectedcount) {
    echo "Success: Tasks count\n";
} else {
    echo "FAIL: Tasks count. Got ".$taskscount." records \n";
}

$tasks = $api->getNewTasks($testperiod,$testuser);

if (count($tasks) == $expectedcountuser) {
    echo "Success: Tasks for user\n";
} else {
    echo "FAIL: Tasks for user. Got ".count($tasks)." records \n";
}

$taskscount = $api->getNewTasksCount($testperiod,$testuser);

if ($taskscount == $expectedcountuser) {
    echo "Success: Tasks count for user\n";
} else {
    echo "FAIL: Tasks count for user. Got ".$taskscount." records \n";
}

/**
* ==================================================================================================
* Tasks comments
*/

$expectedcount = 23;
$expectedcountuser = 17;

$comments = $api->getNewTasksComments($testperiod);


if (count($comments) == $expectedcount) {
    echo "Success: Comments \n";
} else {
    echo "FAIL: Comments. Got ".count($comments)." records \n";
}

$comments = $api->getNewTasksComments($testperiod,$testuser);

if (count($comments) == $expectedcountuser) {
    echo "Success: Comments by user\n";
} else {
    echo "FAIL: Comments by user. Got ".count($comments)." records \n";
}