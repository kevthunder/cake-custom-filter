<?php
	
class BooleanFilterType extends FilterType {

	var $element = 'boolean_filter';
	
	function filter(){
		$schema = $this->Model->schema($this->fieldname);
		$list = array(
			1 => __('Yes',true),
			0 => __('No',true),
		);
		if($schema['null']){
			$list['[[NULL]]'] = __('Null',true);
			$list['[[NOT_NULL]]'] = __('Not null',true);
		}
		return array('list'=>$list);
	}
	
}
?>