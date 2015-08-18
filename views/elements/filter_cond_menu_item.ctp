<?php 
if(empty($order)) $order = 0;
if(!isset($active)) $active = $order === 0;
$title = !empty($this->data['CustomFilterCond'][$order]['title'])?$this->data['CustomFilterCond'][$order]['title']:null;
 ?>
<li class="customFilterCondMenuItem<?php if($active) echo ' active' ?>" for="CustomFilterCondForm<?php echo $order ?>">
	<a href="" class="customFilterCondEdit">#<?php echo $order+1 ?><?php if($title) echo ' - '.$title ?></a> <a href="" class="customFilterCondDelete"><?php AdvTrans::sd('Delete'); ?></a>
</li>