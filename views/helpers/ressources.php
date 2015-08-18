<?php
	
class RessourcesHelper extends AppHelper {

	var $helpers = array('Html');
	
	var $isLayout = false;
	var $paths = array(WWW_ROOT=>'');
	var $sourcePlugin = 'CustomFilter';
	
	var $libs = array(
		'jquery' => array(
			'type' => 'js',
			'match' => 'jquery-[0-9.]+\.min\.js'
		),
		'colorbox' => array(
			'colorbox.css',
			'jquery.colorbox-min.js',
		)
	);
	
	function getPaths(){
		$paths = $this->paths;
		if(!empty($this->sourcePlugin)){
			$ppath = App::pluginPath($this->sourcePlugin);
			if(!empty($ppath)){
				$paths[$ppath . 'webroot'] = Inflector::underscore($this->sourcePlugin);
			}
		}
		return $paths;
	}
	
	function getLib($name,$inline = null){
		if(is_null($inline)){
			$inline = $this->isLayout;
		}
		
		////// get Options //////
		$opt = array();
		
		if(is_array($name)){
			$opt = $name;
			$name = null;
			if(!empty($opt['file'])){
				$name = $opt['file'];
			}
		}
		
		$cached = false;
		$cache = array();
		$cache = Cache::read('html_lib');
		if(!empty($name) && !empty($cache[$name])){
			$opt = $cache[$name];
			$cached = true;
		}elseif(!empty($opt)){
			//do nothing
		}elseif(!empty($this->libs[$name])){
			$opt = $this->libs[$name];
		}else{
			$opt = $name;
		}
		
		if(!empty($opt)){
			if(!$cached){
				////// Nornalize Options //////
				if(!is_array($opt)){
					$opt = array(array('file'=>$opt));
				}elseif(!Set::numeric(array_keys($opt))){
					$opt = array($opt);
				}
				foreach($opt as &$fileOpt){
					if(!is_array($fileOpt)){
						$fileOpt = array('file'=>$fileOpt);
					}
					if(!empty($fileOpt['file'])){
						if(empty($fileOpt['type'])) $fileOpt['type'] = pathinfo($fileOpt['file'], PATHINFO_EXTENSION);
						if($fileOpt['type'][0] == '/') $fileOpt['folder'] = '';
					}
					if(!isset($fileOpt['folder'])) $fileOpt['folder'] = $fileOpt['type'];
					if(!empty($fileOpt['file'])){
						foreach($this->getPaths() as $lpath => $upath){
							$path = $lpath . DS . (!empty($fileOpt['folder'])?str_replace('/',DS,$fileOpt['folder']).DS:'').str_replace('/',DS,$fileOpt['file']);
							if(file_exists($path)){
								$fileOpt['path'] = $path;
								$fileOpt['src'] = '/'.(!empty($upath)?$upath.'/':'').(!empty($fileOpt['folder'])?$fileOpt['folder'].'/':'').$fileOpt['file'];
								break;
							}
						}
					}elseif(!empty($fileOpt['match'])){
						foreach($this->getPaths() as $lpath => $upath){
							$folderName = $lpath . (!empty($fileOpt['folder'])?str_replace('/',DS,$fileOpt['folder']).DS:'');
							$folder = new Folder($folderName);
							$res = $folder->find($fileOpt['match']);
							//debug($res);
							if(!empty($res)) {
								$fileOpt['path'] = $folderName . end($res);
								$fileOpt['src'] = '/'.(!empty($upath)?$upath.'/':'').(!empty($fileOpt['folder'])?$fileOpt['folder'].'/':'').end($res);
								break;
							};
						}
					}
					if(!empty($fileOpt['src']) && !empty($fileOpt['path']) && !preg_match('/[?&]sha1=/',$fileOpt['src'])){
						$fileOpt['src'] .= '?sha1='.sha1_file($fileOpt['path']);
					}
					unset($fileOpt);
				}
				if(!empty($name)){
					$cache[$name] = $opt;
					Cache::write('html_lib', $cache);
				}
			}
			if(!empty($opt)){
				//debug($opt);
				////// Include Files //////
				$out = '';
				foreach($opt as $fileOpt){
					if(!empty($fileOpt['src'])){
						if($fileOpt['type'] == 'js'){
							$out .= $this->Html->script($fileOpt['src'],array('inline'=>$inline));
						}elseif($fileOpt['type'] == 'css'){
							$out .= $this->Html->css($fileOpt['src'],null,array('inline'=>$inline));
						}
					}
				}
				return $out;
			}
		}
	}
	
	function beforeLayout ( ){
		$this->isLayout = true;
	}
}