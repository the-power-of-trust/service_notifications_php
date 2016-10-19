<?php
/**
 * User account read functions
 */
namespace app\Database;

class Mongo extends \Gelembjuk\DB\Base {
    
    protected function Conn()
    {
        return $this->dbobject->getMongoConnection();
    }
    protected function Coll($collection)
    {
        if (trim($collection) == '') {
	        throw new \Exception("Collection name not provided");
	    }
	    
        return $this->dbobject->getMongoCollection($this->table($collection));
    }
    
    protected function getNextID($name, $initialvalue = 1)
    {
        return $this->dbobject->getNextSequence($name, $initialvalue);
    }
    
    protected function cleanRecord($item)
    {
        if (!$item) {
            return null;
        }
        
        unset($item['_id']);
        
        return $item;
    }
    
    protected function getListFromCursor($cursor)
    {
        $list = array();
        
        foreach ($cursor as $doc) {
            $doc = $this->cleanRecord($doc);
            $list[] = $doc;
        }
        
        return $list;
    }
    
    protected function getLastInsertedId() 
    {
        $this->processError(new \Exception("Operation not supported"));
    }
    
    protected function makeObjectId($_id)
    {
		return $this->dbobject->makeObjectId($_id);
    }
    /*
    * All these functions are disabled because SQL is not supported there
    */
    protected function executeQuery($sql){
        $this->processError(new \Exception("Operation not supported"));
    }
    protected function getValue($sql) {
        $this->processError(new \Exception("Operation not supported"));
    }
    protected function getRow($sql) {
        $this->processError(new \Exception("Operation not supported"));
    }
    
    protected function getRows($sql) {
        $this->processError(new \Exception("Operation not supported"));
    }
    
    public function getEmptyRecord($table) {
        $this->processError(new \Exception("Operation not supported"));
    }
    protected function quote($s) {
            try {
                    return $this->dbobject->quote($s);
            } catch (\Exception $exception) {
                    $this->processError($exception);
            }
    }
    
    // high level functions t owork with Mongo results
    protected function find($collection, $query = array(), $fields = array())
    {
        $result = $this->Coll($collection)->find($query, $fields);
        
        return $this->getListFromCursor($result);
    }
    protected function findOne($collection, $query = array(), $fields = array(), $options = array())
    {
        $result = $this->Coll($collection)->findOne($query, $fields);
        
        return $this->cleanRecord($result);
    }
}