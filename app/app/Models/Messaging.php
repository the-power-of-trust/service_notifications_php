<?php

namespace app\Models;

class Messaging extends \Gelembjuk\WebApp\Model {
    protected $templatespath;
    protected $templatesextension;
    protected $pagesfirectory;
    
    protected function sendEmail($template,$data,$email,$from='',$replyto='',$ccemail='',$bccemail='',$textemail = false,$locale = '',
		$onlybuildemail = false) {
        // prepare template
        if ($locale == '') {
            $locale = $this->application->getLocale();
        }
        
        $procoptions = array(
            'compiledir' => $this->application->getOption('htmltemplatesoptions')['compiledir'],
            'extension' => 'htm' // we use htm extension for template files
            );
        
        $options = array(
                'locale' => $locale,
                'deflocale' => $this->application->getConfig('defaultlocale'),
                'templatespath' => $this->application->getOption('emailtemplatespath'),
                'templateprocessorclass' => $this->application->getOption('templatingclass'), // same processor as for html templates
                'procoptions' => $procoptions,
                'logger' => $this->application->getLogger()
                );
        
        if ($options['templatespath'] == '') {
            throw new \Exception('Email templates path not found');
        }
        
        $emails = $this->application->getConfig('emails');
        
        if (!is_array($email) && array_key_exists($email,$emails)) {
            $email = $emails[$email];
        }
        
        if (!is_array($from) && array_key_exists($from,$emails)) {
            $from = $emails[$from];
        }       
        
        if (!is_array($replyto) && array_key_exists($replyto,$emails)) {
            $replyto = $emails[$replyto];
        }
        
        if (!is_array($ccemail) && array_key_exists($ccemail,$emails)) {
            $ccemail = $emails[$ccemail];
        }
        
        if (!is_array($bccemail) && array_key_exists($bccemail,$emails)) {
            $bccemail = $emails[$bccemail];
        }
        
        try {
        
            $options['application'] = $this->application;
            
            $formatter = new \Gelembjuk\Mail\EmailFormat($options);
            
            $outtemplate = null;
            
            if (is_array($template)) {
                $outtemplate = $template['out'];
                $template = $template['template'];
            }
            
            $data['applicationtitle'] = $this->application->getConfig('applicationtitle');
            $data['branding'] = $this->application->getConfig('branding');
            $data['baseurl'] = $this->application->getBasehost();
            $data['locale'] = $this->application->getLocale();
            
            $templatedata = $formatter->fetchTemplate($template,$data,$outtemplate);
            
            
			
			if ($onlybuildemail) {
				return [
					'email' => $email,
					'subject' => $templatedata['subject'],
					'body' => $templatedata['body'],
					'from' => $from,
					'replyto' => $replyto,
					'ccemail' => $ccemail,
					'bccemail' => $bccemail,
					'textemail' => $textemail
				];
			}
			
            $this->sendPreparedEmail($email,$templatedata['subject'],$templatedata['body'],
				$from,$replyto,$ccemail,$bccemail,$textemail);
            
        } catch (\Exception $exception) {
            $this->logQ('Format Email error '.$exception->getMessage(),'sendemail|error');
            return false;
        }
        
        return true;
    }
    protected function sendPreparedEmail($email,$subject,$body,
			$from,$replyto,$ccemail,$bccemail,$textemail)
    {
		// make email sending object
		switch ($this->application->getConfig('mailerclass')) {
			case 'phpmailer':
				$mailer = new \Gelembjuk\Mail\PHPMailer();
				break;
			case 'phpnative':
			case 'mail':
				$mailer = new \Gelembjuk\Mail\PHPNative();
				break;
			default:
				$mailer = new \Gelembjuk\Mail\NullMail();
		}
		
		$maileroptions = $this->application->getConfig('mailersettings');
		$maileroptions['logger'] = $this->application->getLogger();
		$maileroptions['application'] = $this->application;
		
		$mailer->initMailer($maileroptions);
            
		$mailer->sendEmail($email,$subject,$body,
			$from,$replyto,$ccemail,$bccemail,$textemail);
			
		return true;
    }
    public function makeNotificationEmail($userid,$info,$scheduler)
    {
    
		$template = (($scheduler == 'daily')?'daily':'hourly').'notification';
		
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUser($userid);
        
        $data = [];
        
        $data['user_rec'] = $user_rec;
        $data['info'] = $info;
        
		return $this->sendEmail($template,$data,$user_rec['email'],'noreply','','','',false,'',true);
    }
    
    public function sendContactMessage($name,$email,$phone,$message) {
        $data = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message
            );
        
        $replyto = array('address'=>$email,'name'=>$name);
        
        $this->logQ($data,'contact');
        
        $this->sendEmail('contact',$data,'contactadmin','noreply',$replyto);
        
        $this->sendEmail('contactusercopy',$data,$email,'contact');
        
        return true;
    }
    
    public function sendActivationEmail($userid, $activationcode) {
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUser($userid);
       
        $activationlink = $this->application->makeAbsUrlQ("Login",'','do','activate',$activationcode,
                '','','',array('email' => $user_rec['email']));
        
        $this->sendEmail('activate',array('user_rec'=>$user_rec,'activationlink'=>$activationlink),
            $user_rec['email'],'noreply');
        
        return true;
    }
    public function sendRegistrationEmail($userid) {
        $logindb = $this->application->getDBO('Login');
        
        $user_rec = $logindb->getUser($userid);
        
        $this->sendEmail('newaccount',array('user_rec'=>$user_rec),$user_rec['email'],'noreply');
        
        return true;
    }
    public function sendPasswordResetEmail($user_rec,$resetcode) {
        $resetlink = $this->application->makeAbsUrlQ("Login",'','view','forgot',$resetcode,'','','',array('email'=>$user_rec['email']));
        
        $this->sendEmail('resetpassword',array('user_rec'=>$user_rec,'resetlink'=>$resetlink),
            $user_rec['email'],'noreply');
        
        return true;
    }
    public function sendPasswordResetConfirmationEmail($user_rec) {
        $this->sendEmail('resetpasswordconfirm',array('user_rec'=>$user_rec),
            $user_rec['email'],'noreply');
        
        return true;
    }
    public function sendEmailChangeConfirmEmail($email,$user_rec,$confirmcode) {
        
        $confirmlink = $this->application->makeAbsUrlQ("Profile",'','do','changeaccemailconfirm',$confirmcode,'','','',array('email' => $email));
        
        $this->sendEmail('changeemail',array('user_rec'=>$user_rec,'email'=>$email,'confirmlink' => $confirmlink),
            $email,'noreply');
        
        return true;
    }
    
    public function sendPreparedSubsriptionEmails($limit = 0, $timelimit = 0)
    {
        $notdb = $this->application->getDBO('Notification');
		
		$starttime = time();
		
		$total = 0;
		
		$logs = [];
		
		do {
			$email = $notdb->getPreparedNotification();
			echo '-';
			if ($email) {
				
				$this->sendPreparedEmail(
					$email['email'],
					$email['subject'],
					$email['body'],
					$email['from'],
					$email['replyto'],
					$email['ccemail'],
					$email['bccemail'],
					$email['textemail']);
				
				$notdb->removeProcessed($email['_id']['oid']);
				
				$total++;
				
				$logs[] = "Sent to email ".$email['email'];
			}
			
		} while( (time() - $starttime < $timelimit && $timelimit > 0 || $timelimit == 0) && 
			($total < $limit && $limit > 0 || $limit == 0 ) && $email);
		
		return $logs;
    }
}
