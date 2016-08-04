<?php

namespace app\Models;

class Pages extends \Gelembjuk\WebApp\Model {
    protected $templatespath;
    protected $templatesextension;
    protected $pagesdirectory;
    protected $deflocale;
    
    public function init($options = array())
    {
        $this->templatespath = $this->application->getOption('htmltemplatespath');
        $this->templatesextension = $this->application->getOption('htmltemplatesoptions')['extension'];
        
        $this->deflocale = $this->application->getConfig('defaultlocale');
        
        if ($this->templatesextension == '') {
            $this->templatesextension = 'htm';
        }
        
        if (isset($options['pagesdir'])) {
            $this->pagesdirectory = $options['pagesdir']; 
        }
        //$this->debug('pages dir '.$this->pagesdirectory);
    }
    
    protected function checkTemplateFileExists($pageid) 
    {
        
        if (!is_array($this->templatespath)) {
            $paths = array($this->templatespath);
        } else {
            $paths = $this->templatespath;
        }
        
        foreach ($paths as $tmplpath) {
            $path = $tmplpath.$this->pagesdirectory.'/'.$pageid.'.'.$this->templatesextension;
            
            if (file_exists($path)) {
                return true;
            }
        }
        return false;
    }
    
    public function checkPageExists($pageid,$locale = '') 
    {
        if (is_array($this->templatespath)) {
            $found = false;
            
            foreach ($this->templatespath as $path) {
                if ($path != '' && is_dir($path)) {
                    $found = true;
                    break;
                }   
            }
            
            if (!$found) {
                return false;
            }
        } else {
            if ($this->templatespath == '' || !is_dir($this->templatespath)) {
                return false;
            }
        }
        
        if ($locale != '' && $this->checkTemplateFileExists($locale.'/'.$pageid)) {
            return true;
        }
        
        if ($this->getCurLocale() != '' && $this->checkTemplateFileExists($this->getCurLocale().'/'.$pageid)) {
            return true;
        }
        
        if ($this->deflocale != '' && $this->checkTemplateFileExists($this->deflocale.'/'.$pageid)) {
            return true;
        }
        
        if ($this->checkTemplateFileExists($pageid)) {
            return true;
        }
            
        return false;
    }
    
    public function getPageTemplate($pageid) 
    {
        if ($this->getCurLocale() != '' && $this->checkTemplateFileExists($this->getCurLocale().'/'.$pageid)) {
            return $this->getCurLocale().'/'.$pageid;
        }
        
        // default locale has more priority then a page in a top
        if ($this->deflocale != '' && $this->checkTemplateFileExists($this->deflocale.'/'.$pageid)) {
            return $this->deflocale.'/'.$pageid;
        }
        
        if ($this->checkTemplateFileExists($pageid)) {
            return $pageid;
        }

        throw new \Exception('Page file not found');
    }
    
    public function getPageMeta($pageid,$locale = '',$templatepath = '') 
    {
        
        if (is_array($this->templatespath) && $templatepath == '') {
            // check in each path
            $lastexp = null;
            
            foreach ($this->templatespath as $path) {
                try {
                    $meta = $this->getPageMeta($pageid,$locale,$path);
                } catch (\Exception $exception) {
                    $lastexp = $exception;
                }
                
                if ($meta) {
                    return $meta;
                }
                $lastexp = null;
            }
            
            if (is_object($lastexp)) {
                throw $lastexp;
            }
            
            return null;
        }
        
        $path = ($templatepath != '')?$templatepath:$this->templatespath;
        
        if (!$this->checkPageExists($pageid, $locale)) {
            return null;
        }
        
        $metafile = '';
        
        if ($locale != '' && $this->checkTemplateFileExists($locale.'/'.$pageid)) {
            $metafile = $path.$this->pagesdirectory.'/'.$locale.'/metainfo.xml';
            
            if (!file_exists($metafile)) {
                $metafile = '';
            }
        }
        
        if ($metafile == '' && $this->getCurLocale() != '' && $this->checkTemplateFileExists($this->getCurLocale().'/'.$pageid)) {
            $metafile = $path.$this->pagesdirectory.'/'.$this->getCurLocale().'/metainfo.xml';
            
            if (!file_exists($metafile)) {
                $metafile = '';
            }
            
        }
        
        // default locale has more priority then a page in a top
        if ($metafile == '' && $this->deflocale != '' && $this->checkTemplateFileExists($this->deflocale.'/'.$pageid)) {
            $metafile = $path.$this->pagesdirectory.'/'.$this->deflocale.'/metainfo.xml';
            
            if (!file_exists($metafile)) {
                $metafile = '';
            }
        }
        
        if ($metafile == '' && $this->checkTemplateFileExists($pageid)) {
            $metafile = $path.$this->pagesdirectory.'/metainfo.xml';
            
            if (!file_exists($metafile)) {
                $metafile = '';
            }
        }
        
        if ($metafile == '') {
            throw new \Exception('Pages model can not find meta info file');
        }
        
        $xml = @file_get_contents($metafile);
        
        $array = \LSS\XML2Array::createArray($xml);
        
        if (!isset($array['pages'])) {
            throw new \Exception('Pages model can not find pages meta description list');
        }
        
        if (isset($array['pages'][$pageid])) {
            $meta = $array['pages'][$pageid];
            
            static $templateprocessor;
        
            if (!$templateprocessor) {
                $templateprocessor = new \Gelembjuk\Templating\SmartyTemplating();
                
                $templateprocessor->init(array(
                    'compiledir' => $this->application->getOption('htmltemplatesoptions')['compiledir']));
                
                $templateprocessor->setApplication($this->application);
                
                $config = $this->application->getConfig('branding');
                $templateprocessor->setVar('config',$config);
            }
            
            $meta['title'] = $templateprocessor->fetchString($meta['title']);
            $meta['metainfo'] = $templateprocessor->fetchString($meta['metainfo']);
            $meta['metakeywords'] = $templateprocessor->fetchString($meta['metakeywords']);
            
            return $meta;
        }
        
        return null;
    }
    
    protected function getCurLocale() 
    {
        return $this->application->getLocale();
    }
}

