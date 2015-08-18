<?php
	
class ManualFilterType extends FilterType {

	function alterCondition($condition){
		if(!empty($this->filter['CustomFilter']['conditions'])){
			$condition['cond'] = $this->filter['CustomFilter']['conditions'];
		}else{
			$condition['cond'] = true;
		}
	}
	
	
}
?>