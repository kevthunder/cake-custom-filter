<?php
class CustomFiltersUser extends CustomFilterAppModel {
	var $name = 'CustomFiltersUser';
	var $actsAs = array('Containable');
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'CustomFilter' => array(
			'className' => 'CustomFilter.CustomFilter',
			'foreignKey' => 'custom_filter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'Auth.User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>