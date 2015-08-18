<?php

class CustomFilterAppModel extends AppModel {
	var $fieldPrefix = null;
	function invalidate($field, $value = true) {
		if($this->fieldPrefix && strpos($field,$this->fieldPrefix) !== 0){
			$field = $this->fieldPrefix.$field;
		}
		return parent::invalidate($field, $value);
    }
}

?>