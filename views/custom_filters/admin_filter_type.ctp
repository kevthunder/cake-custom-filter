<?php
	$opt['Form'] = $this->CustomFilter->getFormHelper();
	$opt['fieldPrefix'] = 'CustomFilterCond.'.$order.'.';
	echo $this->Form->input($opt['fieldPrefix'].'type',array('value'=>$type,'type'=>'hidden'));
	echo $this->element($element,$opt);
?>