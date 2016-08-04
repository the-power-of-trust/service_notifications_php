<?php

namespace app\Views;

use \Gelembjuk\WebApp\Exceptions\NotFoundException as NotFoundException;
use \Gelembjuk\WebApp\Exceptions\ViewException as ViewException;

class Profile extends DefaultView{
	
	protected function view() {
	    // displya state of account, login type , email etc
	    
		$this->htmltemplate = 'state';
		
		return true;
	}
	
}