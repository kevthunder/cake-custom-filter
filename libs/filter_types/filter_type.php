<?php
	
class FilterType extends Object {
	var $cond = null;
	var $Model = null;
	var $fieldname = null;
	var $element = null;
	var $elemPlugin = null;
	var $name = null;
	var $plugin = null;
	var $defOps = null;
	var $fieldSettings = array();
	
	function __construct($cond,$Model=null){
		$this->cond = $cond;
		if(!is_null($Model)){
			$this->Model = $Model;
		}elseif(!empty($cond['CustomFilter']['model'])){
			$this->Model = ClassRegistry::init($cond['CustomFilter']['model']);
		}
		$this->fieldname = $cond['CustomFilterCond']['field'];
		$mSettings = $this->Model->getfilterSettings();
		if(!empty($mSettings['fieldsOpt'][$this->fieldname])){
			$this->fieldSettings = $mSettings['fieldsOpt'][$this->fieldname];
		}
	}
	function filter(){
		return array('operations'=>$this->getOperations(),'fieldSettings'=>$this->fieldSettings);
	}
	
	function alterCondition($condition){
		if($FilterOperation = $this->currentOperation()){
			$FilterOperation->alterCondition($condition);
		}
	}
	
	function alterFilterTitle($titleParts){
		if($FilterOperation = $this->currentOperation()){
			$titleParts = $FilterOperation->alterFilterTitle($titleParts);
		}
		return $titleParts;
	}
	
	function getElementName(){
		if(!$this->element){
			if($this->name == 'FilterType'){
				$this->element = 'default_filter';
				$this->elemPlugin = 'CustomFilter';
			}else{
				$path = APP;
				if($this->plugin){
					$path = App::pluginPath($this->plugin);
				}
				$path .= 'view'.DS.'elements'.DS.Inflector::underscore($this->name).'_filter';
				if(file_exists($path)){
					$this->element = Inflector::underscore($this->name).'_filter';
					$this->elemPlugin = $this->plugin?$this->plugin:false;
				}else{
					$this->element = 'default_filter';
					$this->elemPlugin = 'CustomFilter';
				}
			}
		}
		if(is_null($this->elemPlugin)){
			$this->elemPlugin = $this->plugin?$this->plugin:false;
		}
		return array('elem'=>$this->element,'plugin'=>$this->elemPlugin);
	}
	
	function currentOperation(){
		if(!empty($this->cond['CustomFilterCond']['op'])){
			App::import('Lib', 'CustomFilter.ClassCollection'); 
			$FilterOperation = ClassCollection::getObject('FilterOperation',$this->cond['CustomFilterCond']['op'],array($this));
			
			return $FilterOperation;
		}
	}
	
	function getOperations(){
		$operations = array();
		if(is_null($this->defOps)){
			$schema = $this->Model->schema($this->fieldname);
			if(in_array($schema['type'],array('string','text'))){
				$this->defOps = array('Equals','Contains','Starts','Ends');
			}else{
				$this->defOps = array('Equals','Bigger','Smaller','Between');
			}
		}
		if(!empty($this->defOps)){
			foreach($this->defOps as $type){
				$FilterOperation = ClassCollection::getObject('FilterOperation',$type,array($this));
				
				$operations[$type] = $FilterOperation;
			}
		}
		$toCheck = ClassCollection::getList('FilterOperation',array('hasMethod'=>'detect'));
		foreach($toCheck as $type){
			if(empty($operations[$type])){
				$FilterOperation = ClassCollection::getObject('FilterOperation',$type,array($this));
				
				$operations[$type] = $FilterOperation;
			}
		}
		return $operations;
	}
}
?>