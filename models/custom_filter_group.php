<?php
class CustomFilterGroup extends CustomFilterAppModel {
	var $name = 'CustomFilterGroup';
	var $actsAs = array('Order');
	var $displayField = 'title';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $hasMany = array(
		'CustomFilter' => array(
			'className' => 'CustomFilter.CustomFilter',
			'foreignKey' => 'custom_filter_group_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	function beforeValidate(){
		if(empty($this->id) && ($existing = $this->getExisting())){
			$this->id = $this->data[$this->alias]['id'] = $existing[$this->alias]['id'];
		}
		if(empty($this->id) && empty($this->data[$this->alias]['or']) && !empty($this->data[$this->alias]['key']) && strpos($this->data[$this->alias]['key'],'::') !== false){
			$this->data[$this->alias]['or'] = 1;
		}
		if(empty($this->id) && empty($this->data[$this->alias]['title']) && ($title = $this->defaultTitle())){
			$this->data[$this->alias]['title'] = $title;
		}
	}
	
	function getExisting($data = null){
		if(is_null($data)) $data = $this->data;
		if(!empty($data[$this->alias]['id'])){
			return $this->find('first',array('conditions'=>array('id'=>$data[$this->alias]['id']),'recursive'=>-1));
		}
		if(!empty($data[$this->alias]['key'])){
			return $this->find('first',array('conditions'=>array('key'=>$data[$this->alias]['key']),'recursive'=>-1));
		}
		if(!empty($data[$this->alias]['title'])){
			return $this->find('first',array('conditions'=>array('title'=>$data[$this->alias]['title']),'recursive'=>-1));
		}
		return null;
	}
	
	function defaultTitle($data = null){
		if(is_null($data)) $data = $this->data;
		if(!empty($data[$this->alias]['key'])){
			if(strpos($data[$this->alias]['key'],'::') !== false){
				list($alias,$field) = explode('::',$data[$this->alias]['key']);
				$Model = ClassRegistry::init($alias);
				return $Model->filterableFields($field);
			}else{
				return __(Inflector::humanize($data[$this->alias]['key']),true);
			}
		}
		return null;
	}
}
?>