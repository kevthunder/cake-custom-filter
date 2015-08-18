<?php

App::import('Lib', 'CustomFilter.AdvTrans');

class CustomFilter extends CustomFilterAppModel {
	var $name = 'CustomFilter';
	var $actsAs = array('Order','Containable');
	var $displayField = 'title';
	
	var $belongsTo = array(
		'CustomFilterGroup' => array(
			'className' => 'CustomFilter.CustomFilterGroup',
			'foreignKey' => 'custom_filter_group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
	
	var $hasMany = array(
		'CustomFilterCond' => array(
			'className' => 'CustomFilter.CustomFilterCond',
			'foreignKey' => 'custom_filter_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'CustomFiltersUser' => array(
			'className' => 'CustomFilter.CustomFiltersUser',
			'foreignKey' => 'custom_filter_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	
	var $virtualFields = array(
		'save' => "NOT(CustomFilter.hidden)",
		'public' => "NOT(CustomFilter.hidden)"
	);
	
	function beforeValidate(){
		if(!isset($this->data[$this->alias]['hidden']) && isset($this->data[$this->alias]['save'])){
			if(isset($this->data[$this->alias]['public'])){
				$this->data[$this->alias]['hidden'] = !$this->data[$this->alias]['save'] || !$this->data[$this->alias]['public'];
				if(!$this->data[$this->alias]['public']){
					$this->data['CustomFiltersUser']['hidden']=false;
				}
			}else{
				$this->data[$this->alias]['hidden'] = !$this->data[$this->alias]['save'];
			}
		}
		if(!empty($this->data[$this->alias]['save'])){
			if(empty($this->data[$this->alias]['title'])){
				$this->invalidate('title','notempty');
			}
		}
		if(!empty($this->data['CustomFilterGroup']['title']) && $this->data['CustomFilterGroup']['title'] == '(auto)'){
			if($this->data['CustomFilterGroup']['field'] != null){
				$this->data['CustomFilterGroup']['title'] = __(Inflector::humanize($this->data['CustomFilterGroup']['field']),true);
			}else{
				$this->data['CustomFilterGroup']['title'] = null;
			}
		}
		if(empty($this->data['CustomFilterGroup']['title']) && empty($this->data[$this->alias]['custom_filter_group_id'])){
			if(!empty($this->data[$this->alias]['save'])){
				$this->CustomFilterGroup->invalidate('title','notempty');
			}elseif(empty($this->data[$this->alias]['id'])){
				if(!empty($this->data['CustomFilterCond']) && count($this->data['CustomFilterCond']) == 1 ){
					$firstCond = reset($this->data['CustomFilterCond']);
					$this->data['CustomFilterGroup']['key'] = $this->data[$this->alias]['model'].'::'.$firstCond['field'];
				}else{
					$this->data[$this->alias]['custom_filter_group_id'] = 1;
				}
			}
		}
		if(!empty($this->data['CustomFilterCond'])){
			unset($this->data['CustomFilterCond']['%%i%%']);
			foreach($this->data['CustomFilterCond'] as $pos => $cond){
				if($pos !== 'delete'){
					$cond = array_merge($this->data,array('CustomFilterCond'=>$cond));
					$this->CustomFilterCond->fieldPrefix = $pos.'.';
					$this->CustomFilterCond->set($cond);
					$this->CustomFilterCond->validates();
				}
			}
			if(empty($this->data[$this->alias]['id']) && empty($this->data[$this->alias]['cond_count'])){
				$this->data[$this->alias]['cond_count'] = count($this->data['CustomFilterCond']);
			}
		}
		if(empty($this->id) && empty($this->data[$this->alias]['title']) && ($title = $this->defaultTitle())){
			$this->data[$this->alias]['title'] = $title;
		}
		return true;
	}
	
	function getFilteredModel($filter){
		if(!empty($filter['CustomFilter']['model'])){
			$Model = ClassRegistry::init($filter['CustomFilter']['model']);
			if($Model) $Model->Behaviors->attach('CustomFilter.CustomFiltered', array());
			return $Model;
		}
	}
	
	function defaultTitle($data=null,$model = false){
		if(is_null($data)) $data = $this->data;
		if(!empty($data[$this->alias]['cond_count']) && $data[$this->alias]['cond_count'] == 1){
			$cond = $data;
			$cond['CustomFilterCond'] = reset($cond['CustomFilterCond']);
			return $this->CustomFilterCond->defaultTitle($cond);
		}
		return null;
	}
	
	function updateCondition($filter){
		$joins = true;
		$conditions = true;
		if(!empty($filter['CustomFilterCond'])){
			$conditions = array();
			foreach($filter['CustomFilterCond'] as $pos => $cond){
				if($pos !== 'delete'){
					$cond = array_merge($filter,array('CustomFilterCond'=>$cond));
					$conditions[] = $this->CustomFilterCond->parseCondition($cond, $joins);
				}
			}
		}
		
		if($conditions !== true){
			$filter[$this->alias]['sql'] = $this->getDataSource()->conditions($conditions, true, false, $this);
		}
		if($joins !== true){
			$filter[$this->alias]['joins'] = $joins;
		}
		return $filter;
	}
	
	function beforeSave(){
		if(!empty($this->data[$this->alias]['model'])){
			if(!empty($this->data['CustomFilterGroup']['title']) || !empty($this->data['CustomFilterGroup']['key'])){
			
				//debug($this->data);
				$group = $this->CustomFilterGroup->getExisting($this->data);
				
				//debug($group);
				if(!$group){
					$group = $this->CustomFilterGroup->save(array_merge(
						array(
							'active' => 1,
						),
						$this->data['CustomFilterGroup']
					));
					$group['CustomFilterGroup']['id'] = $this->CustomFilterGroup->id;
				}
				if($group){
					$this->data[$this->alias]['custom_filter_group_id'] = $group['CustomFilterGroup']['id'];
				}
			}
			$this->data = $this->updateCondition($this->data);
			if(!empty($this->data[$this->alias]['joins'])) $this->data[$this->alias]['joins'] = serialize($this->data[$this->alias]['joins']);
			if(!empty($this->data[$this->alias]['advanced_opt'])) $this->data[$this->alias]['advanced_opt'] = serialize($this->data[$this->alias]['advanced_opt']);
		}
		return true;
	}
	
	function afterSave($created){
		if(!empty($this->data['CustomFilterCond'])){
			foreach($this->data['CustomFilterCond'] as $pos => $cond){
				if($pos !== 'delete'){
					$cond = array_merge($this->data,array('CustomFilterCond'=>$cond));
					$cond['CustomFilterCond']['custom_filter_id'] = $this->id;
					$this->CustomFilterCond->create();
					$this->CustomFilterCond->save($cond);
				}
			}
			if(!empty($this->data['CustomFilterCond']['delete'])){
				$this->CustomFilterCond->deleteAll(array('CustomFilterCond.id'=>$this->data['CustomFilterCond']['delete']));
			}
			
			if(!$created){
				if(!array_key_exists('cond_count',$this->data['CustomFilterCond'])) $this->data['CustomFilterCond']['cond_count'] = $this->field('cond_count');
				$cond_count = $this->CustomFilterCond->find('count',array('conditions'=>array('custom_filter_id'=>$this->id),'recursive'=>-1));
				if($this->data['CustomFilterCond']['cond_count'] != $cond_count){
					$this->save(array('cond_count'=>$cond_count));
				}
			}
		}
		$this->saveMyCustomFilter($this->id,$this->data,$created);
	}
	
	function saveMyCustomFilter($id,$data,$created=false){
		if(!empty($data['CustomFiltersUser']['user_id']) && count($data['CustomFiltersUser']) > 1){
			$this->CustomFiltersUser->create();
			$data['CustomFiltersUser']['custom_filter_id'] = $id;
			if(!$created && empty($data['CustomFiltersUser']['id'])){
				$existing = $this->CustomFiltersUser->find('first',array(
					'field'=>'id',
					'conditions'=>array(
						'user_id'=>$data['CustomFiltersUser']['user_id'],
						'custom_filter_id'=>$id
					),
					'recursive'=>-1
				));
				if($existing){
					$this->CustomFiltersUser->id = $data['CustomFiltersUser']['id'] = $existing['CustomFiltersUser']['id'];
				}
			}
			return $this->CustomFiltersUser->save($data['CustomFiltersUser']);
		}
		return false;
	}
	
	function _optionsToFormatedData($opt,$defOpt){
		if(empty($opt)) return $opt;
		
		$opt = Set::merge($defOpt,$opt);
		$data = array_intersect_key($opt,Set::normalize($opt['models']));
		//debug(Set::normalize($opt['models']));
		foreach(Set::normalize($opt['models']) as $alias => $fowardOpt){
			if(!empty($fowardOpt)){
				//debug(Set::normalize($fowardOpt));
				foreach(Set::normalize($fowardOpt) as $from => $to){
					if(empty($to)) $to = $from;
					if(array_key_exists($from,$opt)){
						$data[$alias][$to] = $opt[$from];
						unset($opt[$from]);
					}
				}
			}else{
				$data[$alias] = array_merge(array_diff_key($opt,$defOpt,Set::normalize($opt['models'])),empty($data[$alias])?array():$data[$alias]);
			}
		}
		if(!empty($data['CustomFilterCond'])  && !Set::numeric(array_keys($data['CustomFilterCond']))){
			$data['CustomFilterCond'] = array($data['CustomFilterCond']);
		}
		return $data;
	}
	
	function getFilter($options){
		$defOpt = array(
			'models'=>array('CustomFilterCond'=>array('field','val1','type'),'CustomFilterGroup'=>array('group'=>'title','group_key'=>'key'),'CustomFilter'),
			'override'=>array(
			),
			'default'=>array(
			),
		);
		
		$opt = Set::merge($defOpt,$options);
		$data = $this->_optionsToFormatedData($opt,$defOpt);
		$override = $this->_optionsToFormatedData($opt['override'],$defOpt);
		$default = $this->_optionsToFormatedData($opt['default'],$defOpt);
		if(empty($data[$this->alias]['model'])) return null;
		
		App::import('Lib', 'CustomFilter.SetMulti');
		
		//debug($data);
		$key = empty($data['CustomFilter']['key'])?null:$data['CustomFilter']['key'];
		if($key){
			$findOpt = array(
				'conditions'=> array('CustomFilter.key'=>$key),
				'recursive' => -1,
			);
			$override = SetMulti::complexMerge($data,$override,2);
		}else{
			$findOpt = array(
				'conditions'=> Set::flatten(array_diff_key($data,array_flip(array('CustomFilterCond')))),
				'contain' => 'CustomFilterGroup',
			);
			if(!empty($data['CustomFilterCond'])){
				$findOpt['conditions']['CustomFilter.cond_count'] = count($data['CustomFilterCond']);
				foreach($data['CustomFilterCond'] as $i => $cond){
					$a = 'CustomFilterCond'.$i;
					$join = array(
						'alias' => $a,
						'table'=> $this->CustomFilterCond->useTable,
						'type' => 'INNER',
						'conditions' => Set::flatten(array($a=>$cond))
					);
					$join['conditions'][] = 'CustomFilter.id = '.$a.'.custom_filter_id';
					$findOpt['joins'][] = $join;
				}
			}
			//debug($findOpt);
		}
		
		$existing = $this->find('first',$findOpt);
		if($existing) $filter = $existing;
		if(empty($existing) || !empty($override)){
			$filter = $default;
			if($existing){
				$filter = SetMulti::complexMerge($filter,$existing,2);
			}else{
				$filter = SetMulti::complexMerge($filter,$data,2);
			}
			$filter = SetMulti::complexMerge($filter,$override,2);
			//debug($filter);
			$this->create();
			if($this->save($filter)){
				return $this->id;
			}
		}else{
			return $existing['CustomFilter']['id'];
		}
	}
	
	function afterFind($results){
		foreach($results as &$res){
			if(!empty($res[$this->alias]['joins'])) $res[$this->alias]['joins'] = unserialize($res[$this->alias]['joins']);
			if(!empty($res['joins'])) $res[$this->alias]['joins'] = unserialize($res['joins']);
			if(!empty($res[$this->alias]['advanced_opt'])) $res[$this->alias]['advanced_opt'] = unserialize($res[$this->alias]['advanced_opt']);
			if(!empty($res['advanced_opt'])) $res[$this->alias]['advanced_opt'] = unserialize($res['advanced_opt']);
		}
		return $results;
	}
	
	
}
?>