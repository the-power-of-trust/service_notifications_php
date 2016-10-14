<?php
/**
 * User account read functions
 */
namespace app\Engines\Database;

class MongoEngine implements \Gelembjuk\DB\EngineInterface {
	use \Gelembjuk\Logger\ApplicationLogger;
	
	protected $application;
	
	protected $connection;
	protected $connectioncreatetime = 0;
	protected $connectiontimelimit = 0;
	protected $connectioncharset = '';
	protected $namescharset = '';
	
	protected $dbhost = '';
	protected $dbname = '';
	protected $dbuser = '';
	protected $dbpassword = '';
	
	protected $tableprefix;
	
	public function __construct($options = array()) {
		$this->application = null;
		
		if (isset($options['application'])) {
			$this->application = $options['application'];
		}
		
		$this->connection = NULL;
		$this->dbhost = $options['host'];
		$this->dbname = $options['database'];
		$this->dbuser = $options['user'];
		$this->dbpassword = $options['password'];
		
		$this->connectiontimelimit = ($options['connectiontimelimit'] > 0)?$options['connectiontimelimit']:0;
		
		$this->tableprefix = ($options['tableprefix'] != '')?$options['tableprefix']:'';
	}
	
	public function getMongoConnection()
	{
	    return $this->getConnection();
	}
	public function getMongoCollection($collection)
	{
	    if (trim($collection) == '') {
	        throw new \Exception("Collection name not provided");
	    }
	    return $this->getConnection()->$collection;
	}
	
	public function getNextSequence($name)
	{
	    $collection = $this->getConnection()->seq;
	    
	    $retval = $collection->findAndModify(
	        array('_id' => $name),
	        array('$inc' => array("seq" => 1)),
	        null,
	        array(
	            "new" => true,
	            "upsert" => true,
	        )
	    );
	    return $retval['seq'];
	}
	
	protected function profilerAction($type,$time,$string) {
		if ($this->application) {
			return $this->application->profilerAction($type,$time,$string);
		}
		return null;
	}
	
	protected function getConnection()
	{
		if ($this->connection !== NULL) {
			if ($this->connectiontimelimit > 0 && 
				$this->connectioncreatetime > 0 && 
				time() - $this->connectioncreatetime > $this->connectiontimelimit) {
				$this->closeConnection();
			} else {
				return $this->connection;
			}
		}

		$connstart = microtime(true);
		
		$att=0;
		
		$connstring = "mongodb://$this->dbhost";
		
		$options = array();
		
		if ($this->dbuser != '') {
		    $options = ["username" => $this->dbuser, "password" => $this->dbpassword];
		}
		
		$mongolient = null;
		
		do {
			// hide errors. 
			$mongolient = new \MongoClient($connstring,$options);
			$att++;

			if (!$mongolient && $att<4) {
				sleep(1);	// try again in 1 sec
			}

		} while (!$mongolient && $att<4);
		
		$this->connection = $mongolient->{$this->dbname};
		
		if (!$this->connection) {
			throw new Exceptions\DBException('Can not connect to the DB server: ','','connection',1);
		}
		
		$conntime = microtime(true) - $connstart;
		
		$this->profilerAction('dbconn',$conntime,"Database connection time $conntime");
		
		$this->connectioncreatetime = time();

		return $this->connection;
	}

	public function closeConnection() {
		if ($this->connection != NULL) {
		    // probably it is needed to call some function to close a connection
			$this->connection=NULL;		
			$this->connectioncreatetime = 0;
			return TRUE;
		}
		return FALSE;
	}

	public function getRows($query)
	{
		throw new \Exception("Operation not supported");
	}


	public function getRow($query) {
		throw new \Exception("Operation not supported");
	}

	public function getValue($query) {
		throw new \Exception("Operation not supported");
	}

	public function executeQuery($query) {
		throw new \Exception("Operation not supported");
	}

	public function getLastInsertedId() {
		throw new \Exception("Operation not supported");
	}

	public function quote($s) {
		throw new \Exception("Operation not supported");
	}

	public function getTablePrefix() {
		return $this->tableprefix;
	}
}