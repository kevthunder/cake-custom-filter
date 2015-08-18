<?php
	echo $this->element('not_field',array('plugin'=>'custom_filter','Form'=>$Form,'fieldPrefix'=>$fieldPrefix));
	$vals = array(
		$fieldPrefix.'val1' => array_merge($fieldSettings,array('label'=>__('Equals',true),'type'=>'datetime','class'=>'customFilterCondVal1')),
	);
	if(in_array('Sparkform',App::Objects('plugin'))){
		$vals[$fieldPrefix.'val1']['type'] = 'datepicker';
		$vals[$fieldPrefix.'val1']['js'] = false;
		$vals[$fieldPrefix.'val1']['class'] .= ' filter_datepicker';
	}
	echo $this->element('operations',array('plugin'=>'custom_filter','valFields'=>$vals,'operations'=>$operations,'Form'=>$Form,'fieldPrefix'=>$fieldPrefix));
	
?>