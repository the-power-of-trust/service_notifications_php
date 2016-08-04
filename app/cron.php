<?php

$application = require_once('init.inc.php');

$application->addOption('RunMode','cron');
$application->addOption('DefaultController','cron');

$application->action();
