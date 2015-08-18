<?php
	echo $this->element('not_field',array('plugin'=>'custom_filter','Form'=>$Form,'fieldPrefix'=>$fieldPrefix));
	echo $Form->input($fieldPrefix.'val1',array('label'=>__(Inflector::humanize($submodel),true),'options'=>$list,'class'=>'customFilterCondVal1'));
?>