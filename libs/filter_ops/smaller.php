<?php
	
class SmallerFilterOperation extends FilterOperation {
	var $label = 'Smaller Than';
	
	function alterCondition($condition){
		$tmp = $condition['centerCond'];
		$condition['centerCond'] = array();
		foreach($tmp as $key => $val){
			$key .= ' <';
			$condition['centerCond'][$key] = $val;
		}
	}
	
	function getLabel($translate=true){
		$label = is_a($this->Type, 'DateFilterType')?'Before':$this->label;
		if($translate){
			return AdvTrans::sd($label,true);
		}else{
			return $label;
		}
	}
}

?>