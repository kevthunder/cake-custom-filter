<?php
	echo $this->element('not_field',array('plugin'=>'custom_filter','Form'=>$Form,'fieldPrefix'=>$fieldPrefix));
	echo $Form->input($fieldPrefix.'val1',array('label'=>AdvTrans::sd('Equals',true),'options'=>$list,'class'=>'customFilterCondVal1'));
?>