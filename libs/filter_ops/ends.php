<?php
	
class EndsFilterOperation extends FilterOperation {
	var $label = 'Ends with';
	
	function alterCondition($condition){
		$tmp = $condition['centerCond'];
		$condition['centerCond'] = array();
		foreach($tmp as $key => $val){
			$key .= ' LIKE';
			$condition['centerCond'][$key] = '%'.$val;
		}
	}
}

?>