<?php
class ClassCollection extends Object {

	//App::import('Lib', 'CustomFilter.ClassCollection'); 
	
	
	/////////////////// Settings ///////////////////
	
	var $types = array(
		'FilterType'=>array(
			'classSufix'=>'FilterType',
			'fileSufix'=>null,
			'paths'=>'%app%/libs/filter_types/',
			'defaultByParent'=> true,
			'setName'=> true,
			'setPlugin'=> true,
			'pluginPassthrough'=>true,
			'singleton'=>false,
			'parent'=>array(
				'name'=>'CustomFilter.FilterType'
			)
		),
		'FilterOperation'=>array(
			'classSufix'=>'FilterOperation',
			'fileSufix'=>null,
			'paths'=>'%app%/libs/filter_ops/',
			'defaultByParent'=> true,
			'setName'=> true,
			'setPlugin'=> true,
			'pluginPassthrough'=>true,
			'singleton'=>false,
			'parent'=>array(
				'name'=>'CustomFilter.FilterOperation'
			)
		),
	);
	var $defaultOptions = array(
		'plugin'=>null,
		'classSufix'=>null,
		'fileSufix'=>null,
		'paths'=>'%app%/libs/',
		'ext'=>'php',
		'parent'=>null,
		'pluginPassthrough'=>false,
		'defaultByParent'=>false,
		'throwException'=>true,
		'setName'=>false,
		'setPlugin'=>false,
		'singleton'=>true,
	);
	var $parentInerit = array(
		'ext','paths'
	);
	var $cache = null;
	
	
	
	/////////////////// Public Static functions ///////////////////
	
	function getList($type,$options=array()){
		$_this =& ClassCollection::getInstance();
		
		if(!is_array($options)){
			$options = array('named'=>$options);
		}
		$defOpt = array(
			'named'=>false,
			'hasMethod'=>array(),
			'pluginPassthrough'=>null,
		);
		$opt = array_merge($defOpt,$options);
		
		$cacheKey = $type.'_'.sha1(serialize($opt));
		if(!$_this->cache){
			$_this->cache = Cache::read('class_collection');
		}
		if(!Configure::read('debug') && isset($_this->cache['lists'][$cacheKey])){
			return $_this->cache['lists'][$cacheKey];
		}
		
		$topt = $_this->types[$type];
		$topt = Set::Merge($_this->defaultOptions,$topt);
		
		if(is_null($opt['pluginPassthrough'])) $opt['pluginPassthrough'] = $topt['pluginPassthrough'];
		$ppaths = $_this->_getPaths($topt,true);
		
		$endsWith = $topt['fileSufix'].'.'.$topt['ext'];
		
		$items = array();
		foreach($ppaths as $plugin => $paths){
			foreach($paths as $path){
				$Folder =& new Folder($path);
				$contents = $Folder->read(false, true);
				foreach ($contents[1] as $item) {
					if (substr($item, - strlen($endsWith)) === $endsWith) {
						$item = substr($item, 0, strlen($item) - strlen($endsWith));
						$fitem = ((!$opt['pluginPassthrough'] && $plugin != 'app')?$plugin.'.':'').$item;
						if(!empty($opt['hasMethod'])){
							$class = $_this->getClass($type,$fitem);
							foreach((array)$opt['hasMethod'] as $method){
								if(!method_exists($class,$method)){
									continue 2;
								}
							}
						}
						if($opt['named']){
							if($plugin != 'app' && $opt['named'] !== 'flat'){
								$items[$plugin][$fitem] = Inflector::humanize($item);
							}else{
								$items[$fitem] = Inflector::humanize($item);
							}
						}else{
							$items[] = $fitem;
						}
					}
				}
			}
		}
		
		
		$_this->cache['lists'][$cacheKey] = $items;
		Cache::write('class_collection', $_this->cache);
		
		return $items;
	}
	
	function getObject($type,$name,$args=array()){
		$_this =& ClassCollection::getInstance();
		
		$options = $_this->getOption($type,$name);
		
		if($options['singleton']){
			$exitant = ClassRegistry::getObject($type.'.'.$name);
			if($exitant){
				return $exitant;
			}
		}
		
		$class = $_this->getClass($type,$options,$info);
		if(!empty($class) && class_exists($class) ) {
			$created = $_this->_createObj($class,$args);
			if($options['setName'] && empty($created->name)){
				$created->name = $options['name'];
			}
			if($options['setPlugin'] && !isset($created->plugin)){
				$created->plugin = $info['plugin'];
			}
			if($created && $options['singleton'] && !$info['isParent']){
				$success = ClassRegistry::addObject($type.'.'.$name, $created);
			}
			return $created;
		}
		return null;
	}
	
	function getClass($type,$name,&$info = array()){
		//this code only handle the cache, the actual logic is in _getClass()
		$info = array(
			'isParent' => false,
			'plugin' => null
		);
		$_this =& ClassCollection::getInstance();
		
		$cacheKey = is_array($name)?sha1(serialize($name)):str_replace('.','_',$name);

		if(!$_this->cache){
			$_this->cache = Cache::read('class_collection');
		}
		if(!Configure::read('debug') && !empty($_this->cache['getClass'][$type]) && array_key_exists($cacheKey,$_this->cache['getClass'][$type])){
		
			$res = $_this->cache['getClass'][$type][$cacheKey];
			
			if(!empty($res['import'])){
				foreach($res['import'] as $iopt){
					if(!App::import($iopt)){
						return null;
					}
				}
			}
		}else{
			$res = $_this->_getClass($type,$name);
			$_this->cache['getClass'][$type][$cacheKey] = $res;
			Cache::write('class_collection', $_this->cache);
		}
		
		if(!empty($res['class'])){
			$info['isParent'] = $res['isParent'];
			$info['plugin'] = $res['plugin'];
			return $res['class'];
		}
		
		if(!empty($res['error'])){
			debug(str_replace(array_keys($res['error'][1]),array_values($res['error'][1]),__($res['error'][0],true)));
		}
		
		return null;
	}
	
	function setType($type,$options){
		if(!isset($this->types[$type])){
			$this->types[$type] = array();
		}
		$this->types[$type] = Set::Merge($this->types[$type],$options);
	}
	
	/////////////////// Internal functions ///////////////////
	
	
	//$_this =& ClassCollection::getInstance();
	function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new ClassCollection();
		}
		return $instance[0];
	}
	
	function parseClassName($options){
		$name = Inflector::camelize($options['name']);
		if(!empty($options['classSufix'])){
			$name .= $options['classSufix'];
		}
		return $name;
	}
	
	function parseImportOption($options){
		$_this =& ClassCollection::getInstance();
		$options = Set::Merge($_this->defaultOptions,$options);
		
		if(strpos($options['name'],'.') !== false){
			list($options['plugin'],$options['name']) = explode('.',$options['name'],2);
		}
		$importOpt = array(
			'type'=>null,
			'name'=>$_this->parseClassName($options),
			'file'=>Inflector::underscore($options['name'])
		);
		if(!empty($options['fileSufix'])){
			$importOpt['file'] .= $options['fileSufix'];
		}
		$importOpt['file'] .= '.'.$options['ext'];
		if(!empty($options['paths'])){
			$importOpt['search'] = $_this->_getPaths($options,true);
		}
		//debug($importOpt);
		
		return $importOpt;
	}
	
	function getPaths($typeOpt){
		$_this =& ClassCollection::getInstance();
		$opt = Set::Merge($_this->defaultOptions,$typeOpt);
		return $_this->_getPaths($opt);
	}
	function _getPaths($typeOpt,$namedPlugin = false){
		$paths = array();
		//debug($typeOpt);
		if(!empty($typeOpt['paths'])){
			if(empty($typeOpt['plugin'])){
				if((!isset($typeOpt['plugin']) || $typeOpt['plugin'] !== false) && ($typeOpt['pluginPassthrough'] || $namedPlugin)){
					$plugins = array_merge(array(null),App::objects('plugin'));
				}else{
					$plugins = array(null);
				}
			}elseif(!is_array($typeOpt['plugin'])){
				$plugins = array($typeOpt['plugin']);
			}else{
				$plugins = $typeOpt['plugin'];
			}
			foreach((array)$typeOpt['paths'] as $path){
				foreach($plugins as $p){
					if(empty($p)){
						$app = APP;
						$p = 'app';
					}else{
						$app = App::pluginPath($p);
					}
					if(!empty($app)){
						$ppath = str_replace('%app%',$app,$path);
						$ppath = str_replace('/',DS,$ppath);
						$ppath = str_replace(DS.DS,DS,$ppath);
						if($namedPlugin){
							$paths[$p][] = $ppath;
						}else{
							$paths[] = $ppath;
						}
					}
				}
			}
		}
		return $paths;
	}
	
	
	function getOption($type,$name){
		$_this =& ClassCollection::getInstance();
		$options = array();
		if(is_array($name)){
			$options = $name;
		}else{
			$options['name'] = $name;
		}
		
		if(!empty($_this->types[$type])){
			$options = Set::Merge($_this->types[$type],$options);
		}
		$options = Set::Merge($_this->defaultOptions,$options);
		
		if(is_null($options['plugin']) && strpos($options['name'],'.') !== false){
			list($options['plugin'],$options['name']) = explode('.',$options['name'],2);
		}
		
		$options['name'] = Inflector::camelize($options['name']);
		
		return $options;
	}
	
	function _createObj($class,$args=array()){
		switch(count($args)){
			case 0:
				return new $class();
			case 1:
				return new $class($args[0]);
			case 2:
				return new $class($args[0],$args[1]);
			case 3:
				return new $class($args[0],$args[1],$args[2]);
			case 4:
				return new $class($args[0],$args[1],$args[2],$args[3]);
			default:
				$class = new ReflectionClass($class);  //PHP5 only
				return $class->newInstanceArgs($args);
		}
	}
	
	function _getClass($type,$name){
		$_this =& ClassCollection::getInstance();
		$options = $_this->getOption($type,$name);
		
		$res = array('class'=>null,'import'=>array(),'isParent'=>false);
		if(!empty($options['parent'])){
			$inerit = array_intersect_key($options,array_flip($_this->parentInerit));
			$parentOpt = Set::Merge($inerit,$options['parent']);
			$parent = $_this->_getClass(null,$parentOpt);
			if(empty($parent)){
				return null;
			}else{
				$res['import'] = $parent['import'];
			}
		};
		
		$importOpt = $_this->parseImportOption($options);
		
		foreach($importOpt['search'] as $plugin => $search){
			foreach($search as $path){
				$iopt = $importOpt;
				$iopt['search'] = $path;
				if(App::import($iopt)){
					$res['import'][] = $iopt;
					$res['class'] = $iopt['name'];
					$res['plugin'] = $plugin=='app'?null:$plugin;
					break 2;
				}
			}
		}
		if(!$res['class']){
			if(!empty($parent) && $options['defaultByParent']){
				$res['isParent'] = true;
				$res['class'] = $parent['class'];
				$res['plugin'] = $parent['plugin'];
			}elseif($options['throwException']){
				return array('error'=>array('%name% not found.',array('%name%'=>$importOpt['name'])));
			}else{
				return null;
			}
		}
		return $res;
	}
}
?>