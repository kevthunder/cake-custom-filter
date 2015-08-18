<?php 
	echo $this->CustomFilter->scripts(); 
?>
<div class="customFilters" url="<?php echo $this->Html->url($normalUrl,true); ?>">

<?php if( !empty($opt['title']) ) { ?>
<h3><?php echo $opt['title'] ?></h3>
<?php } ?>

<?php if( !empty($opt['list']) ) { ?>
<div class="filters">
<?php
	$filterIds = empty($opt['current'])?array():array_keys($opt['current']);
	foreach($opt['listData'] as $group){
		if(empty($group['CustomFilterGroup']['title'])) $group['CustomFilterGroup']['title'] = $opt['emptyGroup'];
		echo '<h4>'.h($group['CustomFilterGroup']['title']).'</h4>';
		echo '<ul>';
		
		$many = !empty($group['CustomFilterGroup']['or']);
		
		$args = $this->passedArgs;
		$active = false;
		if(empty($filterIds)){
			$active = true;
		}else{
			$active = !count(array_intersect($filterIds,array_keys($group['CustomFilter'])));
			$args['filters'] = array_diff($filterIds,array_keys($group['CustomFilter']));
			$args['filters'] = implode(',',$args['filters']);
		}
		if(empty($args['filters'])) $args['filters'] = 'none';
		echo '<li'.($active?' class="active"':'').'>'.$this->Html->link('('.__($many?'All':'None',true).')',$args).'</li>';
		
		foreach($group['CustomFilter'] as $id => $filter){
			$args = $this->passedArgs;
			$active = false;
			if(empty($filterIds)){
				$args['filters'] = $id;
			}else{
				$args['filters'] = $filterIds;
				if(in_array($id,$filterIds)){
					$active = true;
				}
				if(!$many){ 
					$args['filters'] = array_diff($args['filters'],array_keys($group['CustomFilter']));
					$args['filters'][] = $id;
				}elseif($active){
					$args['filters'] = array_diff($args['filters'],array($id));
				}else{
					$args['filters'][] = $id;
				}
				$args['filters'] = implode(',',$args['filters']);
			}
			if(empty($args['filters'])) $args['filters'] = 'none';
			echo '<li'.($active?' class="active"':'').'>';
			if(empty($filter['title'])) $filter['title'] = $opt['emptyFilter'];
			echo $this->Html->link($filter['title'],$args);
			echo ' <span class="actions">';
			if(empty($opt['autoLock'])){
				if(!empty($filter['locked'])){
					echo $this->Html->link(__('Unlock',true),array('plugin'=>'custom_filter','controller'=>'custom_filters','action'=>'lock',$id,0),array('class'=>'customFilterBtUnlock')).' ';
				}else{
					echo $this->Html->link(__('Lock',true),array('plugin'=>'custom_filter','controller'=>'custom_filters','action'=>'lock',$id,1),array('class'=>'customFilterBtLock')).' ';
				}
			}
			if($filter['editable']) echo $this->Html->link(__('Edit',true),array('plugin'=>'custom_filter','controller'=>'custom_filters','action'=>'edit',$id),array('class'=>'customFilterBtEdit')).' ';
			if($filter['deletable']) echo $this->Html->link(__('Delete',true),array('plugin'=>'custom_filter','controller'=>'custom_filters','action'=>'delete',$id),array('class'=>'btDelete'));
			echo '</span></li>';
		}
		echo '</ul>';
	}
?>
</div>
<?php } ?>

<?php
	$actionLinks = array();
	if(!empty($opt['add']))          $actionLinks[] = $this->Html->link($opt['add'], array('plugin'=>'custom_filter', 'controller'=>'custom_filters','action'=>'add','model'=>$opt['model']),array('class'=>'customFilterAddButton'));
	if(!empty($opt['editCurrent']))  $actionLinks[] = $this->Html->link($opt['editCurrent'], $editCurrentUrl,array('class'=>'customFilterBtEdit'));
	if(!empty($opt['reset']))        $actionLinks[] = $this->Html->link($opt['reset'], $resetUrl,array('class'=>'customFilterBtReset'));
	echo implode(' - ',$actionLinks);
?>
</div>