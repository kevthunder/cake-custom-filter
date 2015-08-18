<?php
App::import('Lib', 'CustomFilter.AdvTrans');
	
class CustomFilterHelper extends AppHelper {
	
	var $helpers = array('CustomFilter.Ressources','Html');
	
	function __construct(){
		$formHelper = Configure::Read('CustomFilter.FormHelper');
		if(empty($formHelper)) {
			if( in_array('Sparkform',App::Objects('plugin')) ) {
				$formHelper = 'Sparkform.Sparkform';
			}else{
				$formHelper = 'Form';
			}
		}
		$this->helpers[] = $formHelper;
		$parts = explode('.',$formHelper,2);
		$this->formHelperName = end($parts);
	}
	
	function getFormHelper(){
		return $this->{$this->formHelperName};
	}
	
	function filters($options=array()){
		$defOpt = array(
			'title' => AdvTrans::sd('Filters',true),
			'add' => AdvTrans::sd('Add filter',true),
			'editCurrent' => false,
			'reset' => AdvTrans::sd('Reset',true),
			'resetParams' => array('filters'),
			'emptyFilter' =>  AdvTrans::sd('My custom report',true),
			'emptyGroup' => AdvTrans::sd('Custom Reports',true),
			'list'=>true,
		);
		if(isset($options['editCurrent']) && $options['editCurrent'] === true) $options['editCurrent'] = AdvTrans::sd('Edit Current filter',true);
		
		$excludeGlobals = array('actions','models','currentFilters');
		$gOpt = array_diff_key($this->params['CustomFilter'],array_flip($excludeGlobals));
		if(!empty($options['model']) && !empty($this->params['CustomFilter']['models'][$options['model']])){
			$opt = array_merge($defOpt,$gOpt,$this->params['CustomFilter']['models'][$options['model']],$options);
		}elseif(!empty($this->params['CustomFilter']['models'])){
			$mopt = reset($this->params['CustomFilter']['models']);
			$mopt['model']= key($this->params['CustomFilter']['models']);
			$opt = array_merge($defOpt,$gOpt,$mopt,$options);
		}
		
		if(!empty($opt['model'])){
			$view =& ClassRegistry::getObject('view');
			
			
			$toReset = array_intersect_key($this->params['named'],array_flip($opt['resetParams']));
			unset($toReset['filters']);
			if(empty($opt['current']) && !count($toReset)) {
				$opt['reset'] = false;
			}
			
			$vars = array('plugin' => 'custom_filter', 'opt'=>&$opt, 'formHelper'=>$this->{$this->formHelperName});
			
			if(!empty($opt['current']) && count($opt['current']) == 1) {
				$vars['editCurrentUrl'] = array('plugin'=>'custom_filter', 'controller'=>'custom_filters','action'=>'edit',key($opt['current']));
				if($opt['editCurrent'] == $opt['add']) $opt['add'] = false;
			}else{
				$opt['editCurrent'] = false;
			}
			
			$vars['resetUrl'] = array_diff_key($view->passedArgs,array_flip($opt['resetParams']));
			$vars['resetUrl']['filters'] = 'none';
			$vars['normalUrl'] = empty($view->passedArgs)?array('filters'=>0):$view->passedArgs;
			
			$output = $view->element('filter_list',$vars);

			return $this->output($output);
		}
	}
	
	function scripts(){
		static $loaded;
		if(!$loaded){
			$loaded = true;
			
			if( in_array('Sparkform',App::Objects('plugin')) ) {
				$this->{$this->formHelperName}->datepickerScript('.filter_datepicker',array('constrainInput'=>false));
			}
			
			$this->Ressources->getLib('custom_filter.css');
			$this->Ressources->getLib('custom_filter.js');
			$this->Ressources->getLib('jquery.form.min.js');
			$this->Ressources->getLib('colorbox');
			
			$this->Html->scriptBlock('
				var str_and = "'.AdvTrans::sd('And',true).'";
			',array('inline'=>false));
			
		}
	}
	
	
	function _normalizeAttributesOpt($options, $exclude = null){
		if(array_key_exists('class',$options) && is_array($options['class'])){
			$options['class'] = implode(' ',$options['class']);
		}
		if(!empty($exclude)){
			$options = array_diff_key($options,array_flip($exclude));
		}
		return $options;
	}
	function _parseAttributes($options, $exclude = null, $insertBefore = ' ', $insertAfter = null){
		$options = $this->_normalizeAttributesOpt($options);
		return parent::_parseAttributes($options, $exclude, $insertBefore, $insertAfter);
	}
	
}

?>