<?php

namespace app\Views;

class Pages extends DefaultView{
	
	protected function view() {
		
		$template = $this->gI('id','alphaext');
		
		if ($template == '') {
			throw new NotFoundException($this->mU(''));
		}
		
		$pagesmod = $this->controller->getDefModel();
		
		$metainfo = $pagesmod->getPageMeta($template);
		
		if (!$pagesmod->checkPageExists($template) || !is_array($metainfo)) {
			throw new NotFoundException($this->mU('','Page not found'),'Page not found');
		}
		
		$this->viewdata['html_title'] = $metainfo['title'];
		$this->viewdata['html_metadescription'] = $metainfo['metainfo'];
		$this->viewdata['html_keywords'] = $metainfo['metakeywords'];
		
		$this->htmltemplate = $pagesmod->getPageTemplate($template);
		
		return true;
	}
	protected function getViewFolderName() {
		return $this->controller->getPagesDir();
	}
	
	protected function getPageSubID()
    {
        return $this->htmltemplate;
    }
}