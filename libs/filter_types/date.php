<?php
	
class DateFilterType extends FilterType {

	var $element = 'date_filter';
	
	function detect(){
		$schema = $this->Model->schema($this->fieldname);
		if(in_array($schema['type'],array('date','datetime'))){
			return true;
		}
	}
	
}
?>