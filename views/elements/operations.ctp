<?php
	$vals = array('default' => $valFields);
	if(!empty($operations)){
		$inputOpt = array(
			'class'=>'customFilterCondOp',
			'label'=>AdvTrans::sd('Operator',true),
		);
		foreach($operations as $Op){
			$inputOpt['options'][$Op->name] = $Op->getLabel();
			
			$alter = $Op->alterForm($vals['default'],$fieldPrefix);
			if($alter){
				$vals[$Op->name] = $alter;
			}
		}
		if(count($inputOpt['options']) < 2){
			unset($inputOpt['options']);
			$inputOpt['type'] = 'hidden';
		}
		echo $Form->input($fieldPrefix.'op',$inputOpt);
	}
	echo $this->element('field_variantes',array('plugin'=>'custom_filter','variantes'=>$vals,'attr'=>array('class'=>'operationVariantes'),'Form'=>$Form,'fieldPrefix'=>$fieldPrefix));
	
?>