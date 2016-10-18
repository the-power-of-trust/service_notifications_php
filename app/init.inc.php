<?php

error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

$MYWEBSITE_DIR = dirname(__FILE__) ."/";
$MYWEBSITE_WEB_DIR = $_SERVER['DOCUMENT_ROOT'].'/';
$MYWEBSITE_TEMP_DIR = $MYWEBSITE_DIR ."/../storage/tmp/";

// composer autoload init
$loader = require $MYWEBSITE_DIR .'../src/vendor/autoload.php';
$loader->add('app\\', $MYWEBSITE_DIR);
$loader->add('', $MYWEBSITE_DIR .'../src/manual/');

require_once('config.inc.php');

$options = array(
	'webroot' => $MYWEBSITE_WEB_DIR,
	'tmproot' => $MYWEBSITE_TEMP_DIR,
	'logdirectory' => $MYWEBSITE_DIR ."/../storage/logs/",
	'modelsnamespace' => '\\app\\Models\\',
	'controllersnamespace' => '\\app\\Controllers\\',
	'databasenamespace' => '\\app\\Database\\',
	'viewsnamespace' => '\\app\\Views\\',
	'routersnamespace' => '\\app\\Routers\\',
	'errorhandlerclass' => '\\Gelembjuk\\Logger\\ErrorScreen',
	'errorhandlerobjectoptions' => array(
		'catchwarnings' => true, 
		'catchfatals' => true,
		'catchexceptions' => true,
		'showfatalmessage' => true,
		'showtrace' => true
	),
	'languagespath' => $MYWEBSITE_DIR.'../resources/lang/',
	
	'htmltemplatespath' => $MYWEBSITE_DIR.'../resources/views/',
	'htmltemplatesoptions' => array(
		'extension' => 'htm',
		'compiledir' => $MYWEBSITE_DIR ."/../storage/template_compile/"
		),
	'emailtemplatespath' => $MYWEBSITE_DIR.'../resources/mail/',
	//'mookapi' => true
	);

$application = \app\Application::getInstance();

$application->init(new appConfig(),$options);

return $application;

