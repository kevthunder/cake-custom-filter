<?php
class CustomFilterCond extends CustomFilterAppModel {
	var $name = 'CustomFilterCond';
	var $actsAs = array('Order');
	var $displayField = 'title';
	var $validate = array(
		'field' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'type' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'CustomFilter' => array(
			'className' => 'CustomFilter.CustomFilter',
			'foreignKey' => 'custom_filter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	
	function beforeValidate(){
		if(empty($this->id) && empty($this->data[$this->alias]['type']) && ($type = $this->getType())){
			$this->data[$this->alias]['type'] = $type->name;
		}
		if(empty($this->id) && empty($this->data[$this->alias]['title']) && ($title = $this->defaultTitle())){
			$this->data[$this->alias]['title'] = $title;
		}
	}
	
	function defaultTitle($data=null,$model = false){
		if(is_null($data)) $data = $this->data;
		if(!empty($data[$this->alias]['title'])) return $data[$this->alias]['title'];
		$titleParts = array('%val1%',array());
		if(!empty($data[$this->alias]['not']) || $model){
			$titleParts[1]['not'] = array('%val1%'=>str_replace('%s','%val1%',AdvTrans::sd('Not : %s',true)));
		}
		if($model){
			$titleParts[1]['val1'] = '%val1%';
		}else{
			$titleParts[1]['val1'] = array('%val1%'=>!empty($data[$this->alias]['val1'])?$data[$this->alias]['val1']:null);
		}
		$type = $this->getType($data);
		if($type){
			$titleParts = $type->alterFilterTitle($titleParts);
		}
		if(is_array($titleParts) && !$model){
			$title = $titleParts[0];
			foreach($titleParts[1] as $key => $replace){
				if(!is_array($replace)) $replace = array($key => $replace);
				$title = str_replace(array_keys($replace),array_values($replace),$title);
			}
			return $title;
		}else{
			return $titleParts;
		}
	}
	
	function getType($cond=null,$Model=null){
		if(is_null($cond)) $cond = $this->data;
		App::import('Lib', 'CustomFilter.ClassCollection'); 
        if(empty($Model)) $Model = $this->CustomFilter->getFilteredModel($cond);
		
		$FilterType = null;
		$type = !empty($cond[$this->alias]['type'])?$cond[$this->alias]['type']:null;
		if(!$type){
			$toCheck = ClassCollection::getList('FilterType',array('hasMethod'=>'detect'));
			foreach($toCheck as $ttype){
				if($TestFilterType = ClassCollection::getObject('FilterType',$ttype,array($cond,$Model))){
					if($TestFilterType->detect()){
						$FilterType = $TestFilterType;
						$type = $ttype;
                    }
				}
			}
			if(!$type){
				$schema = $Model->schema($cond[$this->alias]['field']);
				$type = $schema['type'];
			}
		}
		if(empty($FilterType)){
			$FilterType = ClassCollection::getObject('FilterType',$type,array($cond,$Model));
		}
		return $FilterType;
	}
	
	function parseCondition($cond, &$joins = true){
		$field = $cond[$this->alias]['field'];
		if(strpos($field,'.')==false){
			$field = $cond['CustomFilter']['model'] . '.' . $field;
		}
		$val = $cond[$this->alias]['val1'];
		if($val == '[[NULL]]'){
			$val = null;
		}
		if($val == '[[NOT_NULL]]'){
			$val = null;
			$cond[$this->alias]['not'] = empty($cond[$this->alias]['not']);
		}
		$centerCond = array($field => $val);
		if(!empty($cond[$this->alias]['not'])){
			$condition = array('not'=>&$centerCond);
		}else{
			$condition = &$centerCond;
		}
		
		$joins = true;
		
		$FilterType = $this->getType($cond);
		$FilterType->alterCondition(array('centerCond'=>&$centerCond,'cond'=>&$condition,'joins'=>&$joins));
			
		return $condition;
	}
}
?>