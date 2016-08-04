<?php

namespace app\Controllers;

class Pages extends DefaultController {
    // Location of static pages in views
    protected $pagesdir = 'Pages';
    
    public function init() 
    {
        parent::init();
        $this->defmodel = $this->application->getModel('Pages',array('pagesdir' => $this->getPagesDir()));
    }
    
    public function getPagesDir() 
    {
        return $this->pagesdir;
    }
    
    protected function completeUrlOpts($opts) 
    {
        
        if (isset($opts['id']) && !isset($opts['title'])) {
            // get title with models
            try {
                
                $metainfo = $this->defmodel->getPageMeta($opts['id'],$opts['locale']);
                
                if ($metainfo) {
                    $opts['title'] = $metainfo['title'];
                }
            } catch (\Exception $exception) {
                $this->debug('Pages link error '.$opts['id'].' '.$exception->getMessage());
            }
        }
        return parent::completeUrlOpts($opts);
    }
    
}