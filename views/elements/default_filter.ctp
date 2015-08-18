<?php
	echo $this->element('not_field',array('plugin'=>'custom_filter','Form'=>$Form,'fieldPrefix'=>$fieldPrefix));
	$vals = array(
		$fieldPrefix.'val1' => array_merge($fieldSettings,array('label'=>__('Equals',true),'class'=>'customFilterCondVal1')),
	);
	echo $this->element('operations',array('plugin'=>'custom_filter','valFields'=>$vals,'operations'=>$operations,'Form'=>$Form,'fieldPrefix'=>$fieldPrefix));
	
?>