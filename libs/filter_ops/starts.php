<?php
	
class StartsFilterOperation extends FilterOperation {
	var $label = 'Starts with';
	
	function alterCondition($condition){
		$tmp = $condition['centerCond'];
		$condition['centerCond'] = array();
		foreach($tmp as $key => $val){
			$key .= ' LIKE';
			$condition['centerCond'][$key] = $val.'%';
		}
	}
}

?>