<?php
/**
 * User account read functions
 */
namespace app\Engines\Database;

class MongoNewEngine implements MongoEngine {
	
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

}