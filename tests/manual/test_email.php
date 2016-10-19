
<?php

$application = require_once('../../app/init.inc.php');

$mailer = new \Gelembjuk\Mail\PHPMailer();

$maileroptions = $application->getConfig('mailersettings');
$maileroptions['logger'] = $application->getLogger();
$maileroptions['application'] = $application;
		
$mailer->initMailer($maileroptions);


$mailer->sendEmail("tower.grv@gmail.com","POT Test","<h1>Hello</h1>","gelembjuk@gmail.com");
