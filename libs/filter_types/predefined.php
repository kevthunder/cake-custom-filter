<?php
if(function_exists('lcfirst') === false) {
    function lcfirst($str) {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}
	
class PredefinedFilterType extends FilterType {

	var $element = 'predefined_filter';
	var $list = null;
	
	function getList(){
		if(is_null($this->list)){
			$method = lcfirst(Inflector::camelize(Inflector::pluralize($this->fieldname))).'List';
			if($this->_modelhasMethod($this->Model,$method)){
				$this->list = $this->Model->{$method}();
			}
		}
		return $this->list;
	}
	
	function _modelhasMethod($model,$action){
		if(method_exists ($model, $action)){
			return true;
		}
		$behaviorsMethods = $model->Behaviors->methods();
		foreach($behaviorsMethods  as $methods){
			if(in_array($action,$methods)){
				return true;
			}
		}
		return false;
	}

	function detect(){
		if(count($this->getList())){
			return true;
		}
	}
	
	function filter(){
		$list = $this->getList();
		return array('list'=>$list);
	}
	
	function alterFilterTitle($titleParts){
		$list = $this->getList();
		
		if($list && isset($titleParts[1]['val1']['%val1%']) && isset($list[$titleParts[1]['val1']['%val1%']])){
			$titleParts[1]['val1']['%val1%'] = $list[$titleParts[1]['val1']['%val1%']];
		}
		
		return $titleParts;
	}
}
?>