<?php

namespace app\Views;

class Contact extends DefaultView{
    
    protected function view() 
    {
        $this->htmltemplate = 'contact';
        
        $this->viewdata['html_title'] = $this->application->getConfig('branding')['sitename'].
            ': '.$this->_('contact','front');
        
        $this->viewdata['antispam1'] = '1';
        $this->viewdata['antispam2'] = '2';
        
        $this->viewdata['contactemail'] = $this->application->getConfig('emails')['contact']['address'];
        
        return true;
    }
}