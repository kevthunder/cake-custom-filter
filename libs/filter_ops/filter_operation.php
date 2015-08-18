<?php
	
class FilterOperation extends Object {
	var $cond = null;
	var $Model = null;
	var $fieldname = null;
	var $Type = null;
	var $label = null;

	function __construct($Type){
		$this->cond = $Type->cond;
		$this->Model = $Type->Model;
		$this->fieldname = $Type->fieldname;
		$this->Type = $Type;
	}
	
	function alterFilterTitle($titleParts){
	
		$titleParts[1] = array_merge(array('op'=>array('%val1%'=>__($this->getLabel(false).' %val1%',true))),$titleParts[1]);
		
		return $titleParts;
	}
	
	function getLabel($translate=true){
		if(empty($this->label)){
			$this->label = Inflector::humanize($this->name);
		}
		if($translate){
			return AdvTrans::sd($this->label,true);
		}else{
			return $this->label;
		}
	}
	
	function alterCondition($condition){
	
	}
	
	function alterForm($form){
		return false;
	}
}

?>