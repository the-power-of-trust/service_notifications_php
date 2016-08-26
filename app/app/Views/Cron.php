<?php

namespace app\Views;

class Cron extends DefaultView{
	
    public function init()
    {
        parent::init();
        $this->responseformat = 'json';
    }
	protected function view() {
		return true;
	}
}