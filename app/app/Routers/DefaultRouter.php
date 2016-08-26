<?php

namespace app\Routers;

class DefaultRouter extends \Gelembjuk\WebApp\Router {
	public function init() {
		
		if (!is_dir($this->options['languagespath'])) {
			throw new \Exception('Languages directory not found');
		}
		
		$this->controllername = 'DefaultController';
	}
	
	public function getRouterName() {
		$function = new \ReflectionClass(static::class);
		$thisclass = $function->getShortName();
		
		return $thisclass;
	}
	public function getAuthType() 
	{
		return '';
	}
	public function getController() 
	{
		if ($this->input['controller'] == '') {
			return $this->controllername;
		}
		
		return ucfirst($this->input['controller']);
	}
	public function parseUrl($url = '') {
		if ($url == '') {
			$url = $this->getRequestUrlPath();
		}
		
		$parsed = @parse_url($url);
		
		if ($parsed && isset($parsed['path'])) {
			
			// error page displayy
			if (preg_match('!^/error(/|.htm)!',$parsed['path'],$m)) {
				$this->input['view'] = 'error';
				$this->input['responseformat'] = 'html';
				return true; // nothign to check more
				
			} elseif (preg_match('!^/error_(\\d+)(/|.htm)!',$parsed['path'],$m)) {
				$this->input['view'] = 'error';
				$this->input['errornumber'] = $m[1];
				$this->input['responseformat'] = 'html';
				return true; // nothign to check more
			}
			
			// some special format 
			if (preg_match('!^/(json|xml)(/.*?)$!',$parsed['path'],$m)) {
				$parsed['path'] = $m[2];
				$this->input['responseformat'] = $m[1];
			}
			
			if (strpos($parsed['path'],'index.php') == 1) {
				$parsed['path'] = '';
			}
			// get controller name
			if (preg_match('!^/([a-z0-9_-]+)(/?.*?)$!',$parsed['path'],$m)) {
				$parsed['path'] = $m[2];
				$this->input['controller'] = ucfirst($m[1]);
			}
			
			if (strtolower($this->input['controller']) == 'def') {
				// default controller will have short name
				$this->input['controller'] = '';
			}
			
			// do more parsing
			if (preg_match('!^/(v|d)/([a-z0-9_-]+)/?$!i',$parsed['path'],$m)) {
				$this->input[(($m[1]=='v')?'view':'do')] = strtolower($m[2]);
				
			} elseif (preg_match('!^/(v|d)/([a-z0-9_-]+)/([a-z0-9_-]+)/!i',$parsed['path'],$m)) {
				$this->input[(($m[1]=='v')?'view':'do')] = strtolower($m[2]);
				$this->input['id'] = $m[3];
				
			} elseif (preg_match('!^/([^/]+)!',$parsed['path'],$m)) {
				$this->input['id'] = $m[1];
			}
			
			if (!empty($this->input['id'])) {
				$_REQUEST['id'] = $this->input['id'];
			}
		}
		
	}
	
	public function setUpActionInfo() {
		if ($this->getInput('view') != '') {
			$this->actiontype = 'view';
			$this->actionmethod = $this->getInput('view','alpha');
			
		} elseif ($this->getInput('do') != '') {
			$this->actiontype = 'do';
			$this->actionmethod = $this->getInput('do','alpha');
			
		} elseif ($this->getInput('redirect','plaintext') != '') {
			$this->actiontype = 'redirect';
			$this->actionmethod = $this->getInput('redirect','plaintext');
			
		} else {
			$this->actiontype = 'view';
			$this->actionmethod = '';
		}
		
		if ($this->getInput('responseformat','alpha') != '') {
			$this->responseformat = $this->getInput('responseformat','alpha');
			
		} elseif ($this->getInput('format','alpha') != '') {
			$this->responseformat = $this->getInput('format','alpha');
		}
		return true;
	}

	public function getCurrentPageOpts() 
	{
		$opts = array();
		
		if ($this->input['controller'] != '') {
			$opts['controller'] = $this->input['controller'];
		}
		
		foreach (array('id','view','do','responseformat') as $k) {
			if ($this->input[$k] != '') {
				$opts[$k] = $this->input[$k];
			}
		}
		return $opts;
	}
	public function makeUrl($opts = array())
	{
		if (isset($opts['error'])) {
			return '/error/?message='.urlencode($opts['error']);
		}
		
		if ($opts['s'] == 'home') {
			return '/';
		}
		
		if (isset($opts['controller'])) {
			$controller = $opts['controller'];
			unset($opts['controller']);
		} else {
			$controller = $this->getController();
		}
		
		$url = '/';
		
		// add response format
		if (isset($opts['responseformat']) && in_array($opts['responseformat'],array('json','xml'))) {
			$url .= $opts['responseformat'] . '/';
			unset($opts['responseformat']);
		}
		
		if ('DefaultController' != $controller) {
			$url .= strtolower($controller) . '/';
		} else {
			$url .= 'def/';
		}
		
		if (isset($opts['view'])) {
			$url .= 'v/'.$opts['view'].'/';
		} elseif (isset($opts['do'])) {
			$url .= 'd/'.$opts['do'].'/';
		}
		unset($opts['view']);
		unset($opts['do']);
		
		if (isset($opts['id'])) {
			$url .= $opts['id'] . '/';
			unset($opts['id']);
		}
		
		if (isset($opts['title'])) {
			$url .= urlencode($opts['title']) . '.htm';
			unset($opts['title']);
		}
		
		if (isset($opts['random'])) {
			$opts['random'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);;
		}
		
		if (count($opts) > 0) {
			
			$url .= '?';
			
			foreach ($opts as $k=>$v) {
				if ($v == '') {
					continue;
				}
				
				$v = urlencode($v);
				
				if ($k == 'message' && preg_match('!^([a-z])%3A(.+)$!',$v,$m)) {
					$v = $m[1].':'.$m[2];
				}
				
				$url .= $k . '=' . $v.'&';
			}
		}
		
		return $url;
	}
	
	public function detectLocale()
	{
		$locale = $this->application->getConfig('defaultlocale');
		
		if ($locale != '') {
			return $locale;
		}
		return 'en';
	}
}