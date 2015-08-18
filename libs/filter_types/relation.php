<?php
	
class RelationFilterType extends FilterType {

	var $element = 'relation_filter';
	var $SubModel = null;
	function getSubModel(){
		if(!$this->SubModel){
			if(preg_match('/^(.+)_id$/',$this->fieldname,$match)){
				$alias = Inflector::Classify($match[1]);
				if(!empty($this->Model->{$alias})){
					$this->SubModel = $this->Model->{$alias};
				}
			}
		}
		return $this->SubModel;
	}

	function detect(){
		if($this->getSubModel()){
			return true;
		}
	}
	
	function filter(){
		$SubModel = $this->getSubModel();
		
		$list = $SubModel->find('list');
		return array('list'=>$list,'submodel'=>$SubModel->alias);
	}
	
	function alterFilterTitle($titleParts){
		$SubModel = $this->getSubModel();
		if($SubModel && !empty($titleParts[1]['val1']['%val1%'])){
			$entry = $SubModel->find('first',array('fields'=>$SubModel->displayField,'conditions'=>array($SubModel->primaryKey=>$titleParts[1]['val1']['%val1%']),'recursive'=>-1));
			$titleParts[1]['val1']['%val1%'] = $entry[$SubModel->alias][$SubModel->displayField];
		}
		
		return $titleParts;
	}
}
?>