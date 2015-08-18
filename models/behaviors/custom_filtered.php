<?php
class CustomFilteredBehavior extends ModelBehavior
{
	var $settings = array();
	var $defSettings = array(
		'fieldsOpt'=>array(),
		'save'=>false,
		'public'=>true, // true, false, 'allways'
	);
	
	function setup(&$Model, $settings = array()){
		if(empty($settings['fieldsOpt'])) $settings['fieldsOpt'] = array();
		if(!empty($settings['fields'])){
			$settings['fieldsOpt'] = set::merge($settings['fieldsOpt'],array_filter(Set::normalize($settings['fields'])));
			$settings['fields'] = array_keys(Set::normalize($settings['fields']));
		}
		if(empty($this->settings[$Model->alias])) $this->settings[$Model->alias] = $this->defSettings;
		$this->settings[$Model->alias] = set::merge($this->settings[$Model->alias],$settings);
	}
	
	function getfilterSettings($Model){
		return $this->settings[$Model->alias];
	}
	
	function filterableFields($Model,$restrict=null){
		$settings = $this->settings[$Model->alias];
		$fields = $Model->Schema();
		if(!empty($settings['fields'])){
			$fields = array_intersect_key($fields,array_flip($settings['fields']));
		}
		if(!empty($restrict)){
			$fields = array_intersect_key($fields,array_flip((array)$restrict));
		}
		//debug($fields);
		$fieldsChoices = array();
		foreach($fields as $name => $type){
			$title = $name;
			if(preg_match('/^(.+)_id$/',$title,$match)){
				$title = $match[1];
			}
			$title =  __(Inflector::humanize($title),true);
			if(!empty($settings['fieldsOpt'][$name]['label'])) $title = $settings['fieldsOpt'][$name]['label'];
			$fieldsChoices[$name] = $title;
		}
		if(!empty($restrict) && !is_array($restrict)) return reset($fieldsChoices);
		return $fieldsChoices;
	}
	
	function beforeFind($Model,$query){
		//fixes count queries with group
		if($Model->findQueryType = 'count' && !empty($query['group']) && $query['fields'] == 'COUNT(*) AS `count`'){
			$query['fields'] = 'COUNT(DISTINCT '.implode(', ',$query['group']).') AS `count`';
			unset($query['group']);
			//debug($query);
		}
		return $query;
	}
}