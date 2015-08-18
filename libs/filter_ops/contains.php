<?php
	
class ContainsFilterOperation extends FilterOperation {

	function alterCondition($condition){
		$tmp = $condition['centerCond'];
		$condition['centerCond'] = array();
		foreach($tmp as $key => $val){
			$key .= ' LIKE';
			$condition['centerCond'][$key] = '%'.$val.'%';
		}
	}
}

?>