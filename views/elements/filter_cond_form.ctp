<?php 
if(empty($order)) $order = 0;
if(!isset($active)) $active = $order === 0;

$fieldPrefix = 'CustomFilterCond.'.$order.'.';
 ?>
<div class="customFilterCondForm<?php if($active) echo ' active' ?>" pos="<?php echo $order ?>" id="CustomFilterCondForm<?php echo $order ?>">
	<?php
		if(!empty($this->data['CustomFilterCond'][$order]['id'])){
			echo $this->Form->input($fieldPrefix.'id',array('type'=>'hidden','ref'=> 'customFilterCondId'));
		}
		echo $this->Form->input($fieldPrefix.'title',array('type'=>'hidden','ref'=> 'customFilterCondTile'));
		$inputOpt = array(
			'label'=>AdvTrans::sd('Field',true),
			'class'=> 'customFilterCondField',
		);
		if(!empty($fieldsChoices)) {
			$inputOpt['options'] = $fieldsChoices;
			$inputOpt['empty'] = '--- '.AdvTrans::sd('Select',true).' ---';
		}
		echo $this->Form->input($fieldPrefix.'field',$inputOpt);
	?>
	<div class="FilterType" <?php if(!empty($this->data['CustomFilterCond'][$order]['field'])) echo 'for_field="'.$this->data['CustomFilterCond'][$order]['field'].'"' ?> Source="<?php echo $this->Html->url(array('plugin'=>'custom_filter','controller'=>'custom_filters','action'=>'filter_type','%model%','%field%','%order%')); ?>">
		<?php
			echo $this->Form->input($fieldPrefix.'type',array('type'=>'hidden'));
			
			if(!empty($typeData)){
				echo $this->element($typeData['element'],array_merge($typeData['opt'],array('Form'=>$Form,'fieldPrefix'=>$fieldPrefix)));
			}
		?>
	</div>
</div>