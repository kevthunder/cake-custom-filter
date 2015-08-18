<?php
	echo $Form->input($fieldPrefix.'not',array('label'=>AdvTrans::sd('Not',true),'class'=>'customFilterCondNot','after'=>'<span class="note">'.AdvTrans::sd('Results that match this condition will be excluded',true).'</span>'));
?>