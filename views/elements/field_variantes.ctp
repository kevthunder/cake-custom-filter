<?php
	if(count($variantes) > 1){
		echo '<div'.(!empty($attr)?$this->CustomFilter->_parseAttributes($attr):'').'>';
	}
	foreach ($variantes as $vname => $fields) {
		if(count($variantes) > 1){
			if(!is_numeric($vname) && empty($fields['attr']['class'])) $fields['attr']['class'][] = 'variante_'.$vname;
			$fields['attr']['class'][] = 'variante';
			echo '<div'.(!empty($fields['attr'])?$this->CustomFilter->_parseAttributes($fields['attr']):'').'>';
		}
		unset($fields['attr']);
		foreach($fields as $name => $fieldOpt){
			if(count($variantes) > 1){
				$fieldOpt['id'] = $this->Form->domId($name).ucfirst($vname);
				if(!empty($fieldOpt['class'])){
					$fieldOpt['class'] .= ' '.$this->Form->domId($name);
				}else{
					$fieldOpt['class'] = $this->Form->domId($name);
				}
			}
			echo $Form->input($name,$fieldOpt);
		}
		if(count($variantes) > 1){
			echo '</div>';
		}
	}
	if(count($variantes) > 1){
		echo '</div>';
	}
?>