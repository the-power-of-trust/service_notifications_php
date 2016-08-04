<?php

class appConfig {
	public $offline = false;
	public $devsite = false;
	public $localversion = false;
	
	public $loggingfilter = 'all';
	
	public $defaultlocale = 'uk';
	
	// DB settings
	public $database = array(
		'default' => array(
			'engine' => 'app\\Database\\MongoEngine',
			'user' => '',
			'password' => '',
			'database' => 'DATABASENAME',
			'host' => 'localhost:27017', // mondodb server credentials
		)
		);
	// power of trust API host
	public $potapi = [
	    'host' => 'localhost',
	    'port' => 12345
	];
	// email settings
	public $emails = array(
		'from' => array('address'=>'info@webappademo.com','name'=>'WebApp Demo'),
		'contact' => array('address'=>'info@webappademo.com','name'=>'WebApp Demo contact'),
		'noreply' => array('address'=>'info@webappademo.com','name'=>'WebApp Demo noreply'),
		);
		
	public $branding = array(
		'sitename' => 'Інформер "Сили Довіри"',
		'sitenamewithdomain' => 'info.power-of-trust.net'
		);
	
	// email sending
	public $mailerclass = 'null';
	public $mailersettings = array(
		'mailsystem' => 'mail',
		'smtp_host' => 'smtp.gmail.com',
		'smtp_port' => '465',
		'smtp_secure' => true,
		'smtp_auth' => true,
		'smtp_user' => 'xxxxxx@gmail.com',
		'smtp_password' => 'zzzzzzzz'
		);

	public $integrations = array(
        'facebook' => array(
            'api_key' => '',
            'secret_key' => ''
            ),
        'twitter' => array(
            'consumer_key' => '',
            'consumer_secret' => ''
            ),
        'linkedin' => array(
            'api_key' => '',
            'api_secret' => ''
            ),
        'google' => array(
            'application_name' => '',
            'client_id' => '',
            'client_secret' => ''
            )
    );
	
	//salts
	public $system_salt_general = 'CHANGE_SALT';

	// internal api key
	public $internal_api_key = 'SECURE_PASSWORD_FOR_CRON';
}
