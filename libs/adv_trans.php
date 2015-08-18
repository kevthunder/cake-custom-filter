<?php
class AdvTrans extends Object {
	//App::import('Lib', 'CustomFilter.AdvTrans'); 
	
	var $defSuggestedDomain = 'custom_filter';
	
	//$_this =& AdvTrans::getInstance();
	function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new AdvTrans();
		}
		return $instance[0];
	}
	
	function getDefSuggestedDomain(){
		$_this =& AdvTrans::getInstance();
		return $_this->defSuggestedDomain;
	}
	
	function sd($str,$return=false,$domain=null){
		return AdvTrans::suggestedDomain($str,$return,$domain);
	}
	
	function suggestedDomain($str,$return=false,$domain=null){
		if(is_null($domain)) $domain = AdvTrans::getDefSuggestedDomain();
		if(__($str,true) != $str){
			return __($str,$return);
		}else{
			return __d($domain,$str,$return);
		}
	}
	
}