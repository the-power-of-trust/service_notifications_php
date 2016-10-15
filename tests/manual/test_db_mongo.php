<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../../public';

$application = require_once('../../app/init.inc.php');

class Test extends app\Database\Mongo {
    public function getSeq() 
    {
        $list = $this->Coll('seq')->find();
        
        $l = [];
        
        foreach ($list as $o) {
            $l[] = [$o->_id,$o->seq];
        }
        return $l;
        
    }
    public function getNext() 
    {
        return (int) $this->getNextID("users");
    }
}

$tdb = $application->getDBO('\\Test');

$l = $tdb->getSeq();

echo "Seq:\n";

foreach($l as $i) {
    echo $i[0].':'.$i[1]."\n";
}

$nextid = $tdb->getNext();

echo "New gen: $nextid\n";

$l = $tdb->getSeq();

echo "Seq:\n";

foreach($l as $i) {
    echo $i[0].':'.$i[1]."\n";
}
