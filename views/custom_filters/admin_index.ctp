<div class="customFilters index">
	<?php
		echo $this->Form->create('Custom Filter', array('class' => 'search', 'url' => array('action' => 'index')));
		echo $this->Form->input('q', array('class' => 'keyword', 'label' => false, 'after' => $form->submit(__('Search', true), array('div' => false))));
		echo $this->Form->end();
	?>	
	<h2><?php __('Custom Filters');?></h2>
	
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>			
			<th><?php echo $this->Paginator->sort('title');?></th>			
			<th><?php echo $this->Paginator->sort('desc');?></th>			
			<th><?php echo $this->Paginator->sort('model');?></th>			
			<th><?php echo $this->Paginator->sort('sql');?></th>			
			<th><?php echo $this->Paginator->sort('custom_filter_group_id');?></th>			
			<th class="actions"><?php __('Actions');?></th>
		</tr>
		<?php
			$i = 0;
			$bool = array(__('No', true), __('Yes', true), null => __('No', true));
			foreach ($customFilters as $customFilter) {
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
				?>
					<tr<?php echo $class;?>>
						<td class="id"><?php echo $customFilter['CustomFilter']['id']; ?>&nbsp;</td>
						<td class="title"><?php echo $customFilter['CustomFilter']['title']; ?>&nbsp;</td>
						<td class="desc"><?php echo $text->truncate($customFilter['CustomFilter']['desc'], 150, array('exact' => false)); ?>&nbsp;</td>
						<td class="model"><?php echo $customFilter['CustomFilter']['model']; ?>&nbsp;</td>
						<td class="sql"><?php echo $customFilter['CustomFilter']['sql']; ?>&nbsp;</td>
						<td class="custom_filter_group_id"><?php echo $customFilter['CustomFilterGroup']['title']; ?>&nbsp;</td>
						<td class="actions">
							<?php 
								if(!empty($customFilter['CustomFilter']['model'])){
									echo $this->Html->link(__('Edit', true), array('action' => 'edit', $customFilter['CustomFilter']['id']), array('class' => 'edit'));
								}
								echo $this->Html->link(__('Delete', true), array('action' => 'delete', $customFilter['CustomFilter']['id']), array('class' => 'delete'), sprintf(__('Are you sure you want to delete # %s?', true), $customFilter['CustomFilter']['id'])); 
							?>
						</td>
					</tr>
				<?php
			}
		?>
	</table>
	
	<p class="paging">
		<?php
			echo $this->Paginator->counter(array(
				'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
			));
		?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('« '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 |
		<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true).' »', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Custom Filter', true)), array('action' => 'add')); ?></li>	</ul>
</div>