<?php
	
class BetweenFilterOperation extends FilterOperation {

	function alterCondition($condition){
		$tmp = $condition['centerCond'];
		$condition['centerCond'] = array();
		foreach($tmp as $key => $val){
			$key .= ' BETWEEN ? AND ?';
			$condition['centerCond'][$key] = array($this->cond['CustomFilterCond']['val1'],$this->cond['CustomFilterCond']['val2']);
		}
	}
	
	function alterForm($form,$prefix){
		$form[$prefix.'val2'] = $form[$prefix.'val1'];
		$form[$prefix.'val2']['class'] = str_replace('customFilterCondVal1','customFilterCondVal2',$form[$prefix.'val2']['class']);
		if(is_a($this->Type, 'DateFilterType')){
			$form[$prefix.'val1']['label'] = AdvTrans::sd("From",true);
			$form[$prefix.'val2']['label'] = AdvTrans::sd("To",true);
		}else{
			$form[$prefix.'val1']['label'] = AdvTrans::sd("Bigger or equal to",true);
			$form[$prefix.'val2']['label'] = AdvTrans::sd("Smaller or equal to",true);
		}
		
		return $form;
	}
	
}

?>