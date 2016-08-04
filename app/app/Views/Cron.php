<?php

namespace app\Views;

class Cron extends DefaultView{
	
	protected function view() {
		$this->responseformat = 'json';
		return true;
	}
}