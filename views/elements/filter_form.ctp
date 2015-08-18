<?php
	$inputOpt = array();
	if(!empty($this->data['CustomFilter']['model'])) $inputOpt['type']='hidden';
	echo $this->Form->input('model',$inputOpt);
	
?>
	<div class="fieldSection customFilterCondEditor">
		<div class="customFilterCondModel">
			<ul>
			<?php 
				echo $this->element('filter_cond_menu_item',array('order'=>'%%i%%','Form'=>$Form,'plugin'=>'CustomFilter'));
			?>
			</ul>
			<?php 
				echo $this->element('filter_cond_form',array('order'=>'%%i%%','Form'=>$Form,'plugin'=>'CustomFilter'));
			?>
		</div>
		<?php
		if(!empty($this->data['CustomFilterCond'])){
			$conds = $this->data['CustomFilterCond'];
		}else{
			$conds = array(array());
		}
		foreach ($conds as $i => $cond) {
			echo $this->element('filter_cond_form',array('order'=>$i,'Form'=>$Form,'plugin'=>'CustomFilter','typeData'=>!empty($typesData[$i])?$typesData[$i]:null));
		}
		?>
		<div class="customFilterCondList">
			<ul>
			<?php
				foreach ($conds as $i => $cond) {
					echo $this->element('filter_cond_menu_item',array('order'=>$i,'Form'=>$Form,'plugin'=>'CustomFilter'));
				}
			?>
			</ul>
			<a href="" class="customFilterCondAdd"><?php AdvTrans::sd('Add condition'); ?></a>
		</div>
	</div>
	<div class="fieldSection">
	<?php if($settings['save']) { ?>
		<?php
			echo $this->Form->input('save',array('type'=>'checkbox','label'=>AdvTrans::sd('Save',true)));
		?>
		<div class="saveFields"<?php if(empty($this->data['CustomFilter']['save'])) echo ' style="display:none;"' ?>>
		<?php
			if($settings['public'] === 'allways') {
				echo $this->Form->input('public',array('type'=>'hidden'));
			}elseif($settings['public']){
				echo $this->Form->input('public',array('type'=>'checkbox','label'=>AdvTrans::sd('Public',true)));
			}
			echo $this->Form->input('title',array('label'=>AdvTrans::sd('Filter name',true)));
			echo $this->Form->input('CustomFilterGroup.title',array('label'=>AdvTrans::sd('Group',true)));
			echo $this->Form->input('CustomFilterGroup.key',array('type'=>'hidden'));
			echo $this->Form->input('desc',array('label'=>AdvTrans::sd('Tooltip',true),'type'=>'text'));
		?>
		</div>
	<?php }?>
	<?php echo $this->Form->submit(AdvTrans::sd('Submit',true)); ?>
	</div>