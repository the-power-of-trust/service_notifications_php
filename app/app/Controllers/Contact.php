<?php

namespace app\Controllers;

use \Gelembjuk\WebApp\Exceptions\DoException as DoException;

class Contact extends DefaultController {
    protected function doContactmessage() {
        
        // check antispam
        $n1 = $this->getInput('datacheck1');
        $n2 = $this->getInput('datacheck2');
        $nsum = $this->getInput('datacheck');
        
        if ($n1 + $n2 != $nsum) {
            $this->debug("SPAM check failed $n1,$n2,$nsum");
            
            $error = $this->_('spamchecknotpassed');
            throw new DoException(
                $this->makeUrl(array('message'=>'e:'.$error, 'view' => 'contact')),$error,'contact',400,'redirect');
        }
        
        $messagingmodel = $this->application->getModel('Messaging');
        
        $messagingmodel->sendContactMessage(
            $this->getInput('name'),
            $this->getInput('email'),
            $this->getInput('phone'),
            $this->getInput('message'));
        
        if ($this->responseformat != 'html' && $this->responseformat != '') {
            return true;
        }
        
        return array('redirect',
            $this->makeUrl(array('message'=>'s:'.$this->_('contactsuccess','front')))
            );
    }
}
