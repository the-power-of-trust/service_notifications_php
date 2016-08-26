<?php

namespace app\Models;

use Egulias\EmailValidator\EmailValidator;
use \Gelembjuk\WebApp\Exceptions\FormException as FormException;

class Login extends \Gelembjuk\WebApp\Model {
    protected $remembermecodetime = 1209600; // 14 days
    
    public function getAntiSpamData() 
    {
        // get 2 random numbers and remember their sum in a session
        
        $n1 = rand(1,100);
        $n2 = rand(1,100);
        
        $_SESSION['antispamsum'] = $n1+$n2;
        
        return array('a'=>$n1,'b'=>$n2);
    }
  
    public function checkAntiSpam($data) 
    {
    
        if ($_SESSION['antispamsum'] == $data['c']) {
            return true;
        }
        
        return false;
    }
    
    protected function completeUserSignin($userid,$logintype = '', $rememberme = '',$referrer = '') 
    {
        
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserRecord($userid);
        
        if (!$user_rec) {
            throw new \Exception($this->_('usernotfound','account'));
        }
        
        $_SESSION['userid'] = $user_rec['id'];
        
        $this->application->setUserID($user_rec['id']);
        
        // remember in cookies or not
        if ($logintype == 'site') {
            if ($rememberme === 'y' || $rememberme === 'yes' || $rememberme === true ||
                $rememberme === 1 || $rememberme === '1') {
            
                $this->logQ('set cookie for '.$user_rec['id'],'login');
                // remember this user in the cookie
                $autologincode = $this->getAutologinCode($user_rec);
                
                setcookie('remembermecode',$autologincode,time() + $this->remembermecodetime, '/');
            } elseif ($rememberme != '') {
                
                setcookie('remembermecode','',time() + $this->remembermecodetime);
            }
        } elseif ($user_rec['email'] == '') {
            return $this->application->makeUrl('login',array('view'=>'askemail'));
        }
        
        $this->logQ('complete login');
        
        if ($referrer != '') {
            return $referrer;
        }
        
        return $this->application->makeUrl('',array('s'=>'home'));
    }
    
    public function logIn($email,$password,$rememberme = '',$referrer = '') 
    {
        $email = trim($email);
        
        if ($email == '') {
            throw new \Exception($this->_('emailmissed','account'));
        }
        
        $password = trim($password);
        
        if ($password == '') {
            throw new \Exception($this->_('passwordmissed','account'));
        }
        
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserByEmail($email);

        if (!$user_rec) {
            throw new \Exception($this->_('accountnotfound','account'));
        }
        
        if ($user_rec['logintype'] != 'site') {
            throw new \Exception($this->_('usesociallogin','account'));
        }
        
        if (!$this->checkPassword($password,$user_rec['password'],$user_rec['id'])) {
            throw new \Exception($this->_('passworddoesntmatch','account'));
        }
        
        if ($user_rec['active'] == '0') {
            throw new \Exception($this->_('accountisnotactive','account'));
        }
        $this->logQ('before complete '.$rememberme);
        $redirecturl = $this->completeUserSignin($user_rec,'site',$rememberme,$referrer);
                
        return $redirecturl;
    }
    
    public function logOut() 
    {
        $_SESSION['userid'] = 0;   
        unset($_SESSION['userid']);
        $this->application->setUserID(0);
        
        setcookie('remembermecode','',time()+$this->remembermecodetime,'/');
    }
    
    public function getSocialLoginStartUrl($network,$referrer = '') 
    {
        $networkobj = $this->getNetworkObject($network);
        
        $redirecturl = $this->application->makeAbsUrlQ('login','','do','sociallogincomplete',$network);
        
        $_SESSION['lastreferrer'] = $referrer;
        
        $url = $networkobj->getLoginStartUrl($redirecturl);
        
        $_SESSION['socialloginsate_'.$network] = $networkobj->serialize();
        
        return $url;
    }
    
    public function getSocialLoginExtraInputs($network) 
    {
        $networkobj = $this->getNetworkObject($network);
        
        return $networkobj->getFinalExtraInputs();
    }
    
    public function loginWithSocial($network,$arguments) 
    {
        $networkobj = $this->getNetworkObject($network);
        
        $networkobj->unSerialize($_SESSION['socialloginsate_'.$network]);
        // if fails then throws exception and controller will catch
        $profile = $networkobj->completeLogin($arguments);
        
        $this->logQ($profile,'login|sociallogin');
        
        // check if this user is already registered 
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserByExternalAuth($network,$profile['userid']);
        
        if (!$user_rec) {
            //check email is not used in the system
            if ($profile['email'] != '' && $logindb->checkEmailUsed($profile['email'])) {
                throw new \Exception($this->_('socialemailnameisused','account'));
            }
            // register this user
            $userid = $logindb->addUser($network,1,$profile['name'],$profile['email'],'nopassword','s',$profile['userid']);
            
            $user_rec = $logindb->getUser($userid);
            
            $messagingmodel = $this->application->getModel('Messaging');
            
            if ($user_rec['email'] != '') {
                
                $messagingmodel->sendRegistrationEmail($user_rec['id']);
            }
        }
        
        $redirecturl = $this->completeUserSignin($user_rec,$network,false,$_SESSION['lastreferrer']);
        
        return $redirecturl;
    }
    
    protected function getNetworkObject($network) 
    {
        $options = array();
        
        if (is_array($this->application->getConfig('integrations')) &&
            is_array($this->application->getConfig('integrations')[$network])) {
            $options = $this->application->getConfig('integrations')[$network];
        }
        
        return \Gelembjuk\Auth\AuthFactory::getSocialLoginObject($network,$options);
    }
    public function logInWithCode($code) 
    {
		$userid = $this->getUserFromAutologinCode($code);
		
		if ($userid > 0) {
			$this->completeUserSignin($userid,'site');
		}
	}
    public function registerUser($name,$email,$password,$subscribe) 
    {
        $logindb = $this->application->getDBO('Login');
        
        if (trim($email) == '') {
            throw new FormException('email',$this->_('enteryouremail','account'));
        }
        
        $validator = new EmailValidator;
        
        if (!$validator->isValid($email)) {
            throw new FormException('email',$this->_('entervalidemail','account'));
        }
        
        if ($logindb->checkEmailUsed($email)) {
            throw new FormException('email',$this->_('emailnameisused','account'));
        }
        
        if (trim($name) == '') {
            throw new FormException('name',$this->_('enteryourname','account'));
        }

        if (trim($password) == '') {
            throw new FormException('password',$this->_('enterpassword','account'));
        }
        
        // all checks passed
        $passwordhash = $this->getPasswordHash($password);
        
        $userid = $logindb->addUser('site',0,$name,$email,$passwordhash);
        
        $profiledb = $this->application->getDBO('Profile');
        
        $profiledb->setSubscription($userid,$subscribe);
        
       
        // send activation email
        $messagingmodel = $this->application->getModel('Messaging');
        
        $activationcode = $this->getAutologinCode($userid,'', time() + 3600 * 48,$email);
        
        $messagingmodel->sendActivationEmail($userid, $activationcode);
                
        return true;
    }
    
    public function activateUser($code, $email) 
    {
        $logindb = $this->application->getDBO('Login');
       
        $user_id = $this->getUserFromAutologinCode($code, $email, 3600 * 48);

        if ($user_id < 1) {
            throw new \Exception($this->_('activationcodenotfound','account'));
        }
        
        $user_rec = $logindb->getUser($user_id);
        
        if (!$user_rec) {
            throw new \Exception($this->_('activationcodenotfound','account'));
        }
        
        $logindb->activateUser($user_rec['id']);
        
        $messagingmodel = $this->application->getModel('Messaging');
        $messagingmodel->sendRegistrationEmail($user_rec['id']);
        
        // make this user to be loged in
        return $this->completeUserSignin($user_rec,'site','n','');
    }
    public function resendUserActivation($email,$newemail,$returnfinalemail = false) 
    {
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserByEmail($email);
        
        if (!$user_rec) {
            throw new \Exception($this->_('thisemailnotfound','account'));
        }
        
        if ($email != $newemail && $newemail != '') {
            
            // try to set new email address
            $validator = new EmailValidator;
        
            if (!$validator->isValid($newemail)) {
                throw new FormException('email',$this->_('entervalidemail','account'));
            }
            
            if ($logindb->checkEmailUsed($newemail)) {
                throw new FormException('email',$this->_('emailnameisused','account'));
            }
            
            $profiledb = $this->application->getDBO('Profile');
            $profiledb->updateUserEmail($user_rec['id'],$newemail);
            
            $email = $newemail;
        }
        
        $messagingmodel = $this->application->getModel('Messaging');
        
        $activationcode = $this->getAutologinCode($user_rec['id'],'', time() + 3600 * 48,$email);
        
        $messagingmodel->sendActivationEmail($user_rec['id'],$activationcode);
        
        if ($returnfinalemail) {
            return $email;
        }
        
        return true;
    }
    public function sendPasswordResetEmail($email) {
        if (trim($email) == '') {
            throw new \Exception($this->_('enteryouremail','account'));
        }
        
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserByEmail($email);
        
        if (!$user_rec) {
            throw new \Exception($this->_('thisemailnotfound','account'));
        }
        
        $resetcode = $this->getPasswordResetCode($user_rec['email'],$user_rec['password'],$user_rec['id']);
        
        $messagingmodel = $this->application->getModel('Messaging');
        $messagingmodel->sendPasswordResetEmail($user_rec,$resetcode);
        
        return true;
    }
    public function resetPassword($code,$email,$password) {
        if (trim($password) == '') {
            throw new \Exception($this->_('enterpassword','account'));
        }
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserByEmail($email);
        
        if (!$user_rec) {
            throw new \Exception($this->_('thisemailnotfound','account').'. '.$this->_('repeatprocess'));
        }
        
        if (!$this->checkPasswordResetCode($code,$user_rec['email'],$user_rec['password'],$user_rec['id'])) {
            throw new \Exception($this->_('resetcodeiswrong','account').'. '.$this->_('repeatprocess'));
        }
        
        if ($user_rec['logintype'] != 'site') {
            // update login type. it can be old user registered on MEP
            $logindb->updateUserLoginType($user_rec['id'],'site');
        }
        // update the password
        $logindb->updateUserPassword($user_rec['id'],$this->getPasswordHash($password));
        
        $messagingmodel = $this->application->getModel('Messaging');
        $messagingmodel->sendPasswordResetConfirmationEmail($user_rec);
        
        return true;
    }
    
    public function updatePassword($curpassword,$password) 
    {
        $this->signinRequired();
        
        if (trim($password) == '' || trim($curpassword) == '') {
            throw new \Exception($this->_('enterpassword','account'));
        }
        
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserRecord($this->getUserID());
        
        if ($user_rec['logintype'] != 'site') {
            // update login type. it can be old user registered on MEP
            $logindb->updateUserLoginType($user_rec['id'],'site');
        }
        
        if (!$this->checkPassword($curpassword,$user_rec['password'])) {
            throw new \Exception($this->_('curpassworddoesntmatch','account'));
        }
        
        if ($user_rec['active'] == '0') {
            throw new \Exception($this->_('accountisnotactive','account'));
        }
        
        $logindb->upgradePassword($user_rec['id'],$this->getPasswordHash($password));
        
        return true;
    }
    public function changeUserEmail($email) {
        $this->signinRequired();
        
        // check this email is used somewhere else
        $logindb = $this->application->getDBO('Login');
        
        // try to set new email address
        $validator = new EmailValidator;
    
        if (!$validator->isValid($email)) {
            throw new FormException('email',$this->_('entervalidemail','account'));
        }
        
        if ($logindb->checkEmailUsed($email)) {
            throw new FormException('email',$this->_('emailnameisused','account'));
        }
        
        // make confirm code
        $user_rec = $logindb->getUserRecord($this->getUserID());
        
        $confirmcode = $this->getAutologinCode($user_rec['id'],'', 0,$email);
        
        $messagingmodel = $this->application->getModel('Messaging');
        $messagingmodel->sendEmailChangeConfirmEmail($email,$user_rec,$confirmcode);
        
        return true;
    }
    public function changeUserEmailConfirm($email,$code) {
        $userid = $this->getUserFromAutologinCode($code,$email,10000);
        
        if ($userid < 1) {
            throw new \Exception($this->_('emailconfirmationfailedtryagain','account'));
        }
        
        $profiledb = $this->application->getDBO('Profile');
        // check if existent user
        
        $user_rec = $profiledb->getUserRecord($userid);
        
        if (!$user_rec) {
            throw new \Exception($this->_('usernotfound','account'));
        }
        
        $profiledb->updateUserEmail($userid,$email);
        
        return true;
    }
    
    public function setUserEmail($email) {
        $this->signinRequired();
        
        // try to set new email address
        $validator = new EmailValidator;
    
        if (!$validator->isValid($email)) {
            throw new FormException('email',$this->_('entervalidemail','account'));
        }
        
        $logindb = $this->application->getDBO('Login');
        
        if ($logindb->checkEmailUsed($email)) {
            throw new FormException('email',$this->_('emailnameisused','account'));
        }
        
        $profiledb = $this->application->getDBO('Profile');
        $profiledb->updateUserEmail($this->getUserID(),$email);
        
        $user_rec = $logindb->getUserRecord($this->getUserID());
        
        if ($user_rec['usertype'] == '') {
            return $this->application->makeUrlQ('login','','view','asktype');
        }
        
        return '';
    }
    protected function checkPasswordResetCode($code,$email,$currentpasshash,$userid) 
    {
        if (!preg_match('!^(.+)-(.+)$!',$code,$m)) {
            return false;
        }
        
        $code = $m[1];
        $randomnumber = $m[2];
        
        if ($code == $this->getHashed($email.$currentpasshash.$userid.$randomnumber)) {
            return true;
        }
        return false;
    }
    protected function getPasswordResetCode($email,$currentpasshash,$userid) 
    {
        $randomnumber = (string)rand(1000,9999);
        
        $code = $this->getHashed($email.$currentpasshash.$userid.$randomnumber);
        
        return $code.'-'.$randomnumber;
    }
    
    protected function upgradePassword($userid,$password)
    {
        if ($userid < 1) {
            return false;
        }
        
        $logindb = $this->application->getDBO('Login');
        
        $logindb->updateUserPassword($userid,
            $this->getPasswordHash($password));
        
        return true;
    }
    protected function checkPassword($password,$passwordhash, $userid = 0)
    {
        // try all possible combinations. as we have different salts for 2 sites
        
        if ($this->validatePassword($password,$passwordhash)) {
            return true;
        }
        
        if ($passwordhash == 'PLAIN:'.$password) {
            $this->upgradePassword($userid,$password);
            return true;
        }
        
        return false;
    }
    protected function getHashed($string,$emptysalt = false,$salt = '') {
        if ($salt == '') {
            $salt = $this->application->getConfig('system_salt_general');
        }
        
        if ($emptysalt) {
            $salt = '';
        }
        
        $this->debug('get hash for '.$string.' and salt '.$salt);
        
        $hash = sha1($string.$salt);
        
        for ($i = 0;$i < 500;$i++) {
            $hash = md5($hash.$salt);
        }
        
        return $hash;
    }
    protected function getUserFromAutologinCode($autologincode,$extratext = '',$expirationtime = 0) {
        if (!preg_match('!^(.+)-(.+)-(.+)-(.+)$!',$autologincode,$m)) {
            return false;
        }
        
        $randomnumber = $m[2];
        $time = $m[3];
        $userid = $m[4];
        
        $this->logQ('check '.$autologincode);
        
        if ($autologincode == $this->getAutologinCode($userid,$randomnumber,$time,$extratext)) {
            
            if ($expirationtime == 0) {
                $expirationtime = $this->remembermecodetime;
            }
            
            if ($time < time() - $expirationtime) {
                return 0; // the code is too old
            }
            return $userid;
        }
        return 0;
    }
    
    protected function getAutologinCode($userid,$randomnumber = '', $time = 0,$extratext = '') {
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUserRecord($userid);
        
        if ($randomnumber == '') {
            $randomnumber = (string)rand(1000,9999);
            $time = time();
        }
        
        $autologincode = $user_rec['password'].$user_rec['created'].$user_rec['id'].$randomnumber.$time.$extratext;
        
        $this->logQ('gen hash from '.$autologincode,'login');
        
        $code = $this->getHashed($autologincode);
        
        return $code.'-'.$randomnumber.'-'.$time.'-'.$user_rec['id'];
    }
    public function getUserRecord($user_rec = 0) {
        if ($user_rec === 0) {
            $user_rec = $this->getUserID();
        }
        $userdb = $this->application->getDBO('User');
        
        $user_rec = $userdb->getUserRecord($user_rec);
        
        if (!$user_rec) {
            $user_rec = array('id' => 0);
        }
        return $user_rec;
    }
    
    protected function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    protected function validatePassword($password,$hashedpassword)
    {
        return password_verify ( $password, $hashedpassword );
    }
}
