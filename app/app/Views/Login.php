<?php

namespace app\Views;

use \Gelembjuk\WebApp\Exceptions\NotFoundException as NotFoundException;
use \Gelembjuk\WebApp\Exceptions\ViewException as ViewException;

class Login extends DefaultView{
	
	protected function view() {
	
		// if user is loged in then redirect to home page
		if ($this->application->getUserID() > 0) {
			return array('redirect',$this->application->makeUrl('def',array('s'=>'home')));
			//throw new ViewException($this->mU(''),'User is already loged in',403);
		}
		$this->viewdata['email'] = $this->gI('email','plainline');
		$this->htmltemplate = 'login';
		
		return true;
	}
	protected function viewRegister(){
		return $this->viewRegistration();
	}
	protected function viewRegistration() {
		$this->goToHomeIfIn();
		
		$loginmod = $this->controller->getDefModel();
		
		$this->viewdata['antispam'] = $this->controller->getDefModel()->getAntiSpamData();
		$this->viewdata['registeredemail'] = '';
		
		$this->htmltemplate = 'registration';
		
		$this->viewdata['html_title'] = $this->_('registrationon','account',$this->application->getConfig('branding')['maintitle']);
		
		return true;
	}
	protected function viewRegistered() {
		$this->goToHomeIfIn();
		
		$this->viewdata['registeredemail'] = $this->getInput('email','plainline');
		
		$this->htmltemplate = 'registered';
		
		$this->viewdata['html_title'] = $this->_('registered','account');
		
		return true;
	}
	protected function viewActivated() {
		$this->htmltemplate = 'activated';
		
		$this->viewdata['html_title'] = $this->_('accountactivated','account');
		
		return true;
	}
	protected function viewCheckdata() {
		$this->goToHomeIfIn();
		// forse jsondata return format
		$this->responseformat = 'jsondata';
		
		$this->viewdata = array();
		
		$message = '';
		
		$field = $this->gI('field');
		
		$this->viewdata['value'] = $this->gI('value');
		$this->viewdata['valid'] = '1';
		
		$loginmod = $this->controller->getDefModel();
		
		if (trim($this->viewdata['value']) == '') {
			$message = $this->_('enterloginname','account');
		} elseif ($loginmod->checkFieldUsed($this->viewdata['value'],$field)) {
			$message = $this->_('loginnameisused','account');;
		}
		
		if ($message != '') {
			$this->viewdata['valid'] = '0';
			$this->viewdata['message'] = $message;
		}
		
		return true;
	}
	protected function viewForgot() {
		$this->goToHomeIfIn();
		
		$this->htmltemplate = 'forgotpassword';
	
		$this->viewdata['code'] = $this->gI('id','plainline');
		$this->viewdata['email'] = $this->gI('email','plainline');
		
		$this->viewdata['html_title'] = $this->_('passwordforgotn','account');
	
		return TRUE;
	}
	protected function viewAskemail() {
		$this->signinRequired();
		
		$this->htmltemplate = 'askemail';
		
		$this->viewdata['html_title'] = $this->_('provideemail','account');
		
		return true;
	}
}