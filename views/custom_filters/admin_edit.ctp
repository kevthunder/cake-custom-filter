<?php
	$this->CustomFilter->scripts();
	$Form = $this->CustomFilter->getFormHelper();
?>
<div class="customFiltersForm">
	<?php echo $this->Form->create('CustomFilter');?>
		<fieldset>
			<legend><?php __('Edit Filter'); ?></legend>
			<?php
				echo $this->Form->input('id');
				echo $this->element('filter_form',array('Form'=>$Form,'plugin'=>'CustomFilter'));
			?>
		</fieldset>
	<?php echo $this->Form->end();?>
</div>