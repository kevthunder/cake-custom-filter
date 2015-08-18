<?php
class CustomFilterComponent extends Object
{
	//var $components = array('RequestHandler', 'Session');

	var $settings = array();
	var $defSettings = array(
		'actions'=>array('admin_index'),
		'models'=>array(),
		'autoLock'=>false,
	);
	var $controller;
	function initialize(&$controller, $settings)
	{
		$this->settings = array_merge($this->defSettings,$settings);
	}
	
	function startup(&$controller)
	{
		if(empty($this->settings['models']) && !empty($controller->modelClass)){
			$this->settings['models'] = array($controller->modelClass => array());
		}elseif(!is_array($this->settings['models'])){
			$this->settings['models'] = array($this->settings['models'] => array());
		}else{
			$this->settings['models'] = Set::normalize($this->settings['models']);
		}
		$this->controller = $controller;
		
		$this->CustomFilter = ClassRegistry::init('CustomFilter.CustomFilter');
		
		$this->settings['currentFilters'] = $filterIds = $this->getCurentFilterIds();
		if(in_array($controller->params['action'],$this->settings['actions'])){
		
			$controller->paginate = $this->applyFilters($controller->paginate,'all',$filterIds);
			
			$controller->helpers['CustomFilter.CustomFilter'] = array();
			foreach($this->settings['models'] as $alias => &$opt){
				if($this->settings['autoLock']){
					$this->lockFilterSelection($alias,$filterIds);
				}
				if(!empty($controller->$alias)){
					$controller->$alias->Behaviors->attach('CustomFilter.CustomFiltered', $opt);
				}
				$raw = $this->findActiveFilters($alias,$this->controller->user,$filterIds);
				//debug($raw);
				$opt['listData'] = array();
				foreach($raw as $filter){
					if(!empty($filter['MyCustomFilter'])) $filter['CustomFilter'] = array_merge($filter['CustomFilter'],$filter['MyCustomFilter']);
					$opt['listData'][$filter['CustomFilterGroup']['id']]['CustomFilter'][$filter['CustomFilter']['id']] = &$filter['CustomFilter'];
					$opt['listData'][$filter['CustomFilterGroup']['id']]['CustomFilterGroup'] = $filter['CustomFilterGroup'];
					if(in_array($filter['CustomFilter']['id'],$filterIds)){
						$opt['current'][$filter['CustomFilter']['id']] = &$filter;
						$opt['listData'][$filter['CustomFilterGroup']['id']]['CustomFilter'][$filter['CustomFilter']['id']]['current'] = 1;
					}
				}
			}
			
			
			$controller->params['CustomFilter'] = $this->settings;
		}
	}
	
	function findActiveFilters($model,$user=null,$curentFilterIds=null){
		if($user){
			$activeCond = array('or'=>array(
				array(
					'MyCustomFilter.hidden IS NULL',
					'not'=>array('CustomFilter.hidden'=>1)
				),
				array(
					'MyCustomFilter.hidden IS NOT NULL',
					'not'=>array('MyCustomFilter.hidden'=>1)
				),
			));
		}else{
			$activeCond = array('or'=>array('not'=>array('CustomFilter.hidden'=>1)));
		}
		if(!empty($curentFilterIds)){
			$activeCond['or']['CustomFilter.id'] = $curentFilterIds;
		}
		if(count($activeCond['or']) == 1) $activeCond = $activeCond['or'];
		$findOpt = array(
			'fields'=>array(
				'CustomFilter.id','CustomFilter.model','CustomFilter.title','CustomFilter.desc','CustomFilter.editable','CustomFilter.deletable',
				'CustomFilterGroup.id','CustomFilterGroup.title','CustomFilterGroup.or'
			),
			'conditions'=>array(
				'CustomFilter.model'=>$model,
				$activeCond
			),
			'order'=>'',
			'contain'=>array('CustomFilterGroup')
		);
		if($user){
			$findOpt['contain'][] = 'MyCustomFilter';
			$findOpt['fields'][] = 'MyCustomFilter.locked';
			$this->CustomFilter->bindModel(
				array('hasOne' => array(
						'MyCustomFilter' => array(
							'className' => 'CustomFilter.CustomFiltersUser',
							'foreignKey' => 'custom_filter_id',
							'conditions' => array('MyCustomFilter.user_id'=>$user['User']['id']),
						)
					)
				)
			);
		}
		return $this->CustomFilter->find('all',$findOpt);
	}
	
	function lockFilterSelection($model,$filterIds = null){
		if(is_null($filterIds)) $filterIds = getCurentFilterIds($model);
		if($this->controller->user && $model){
			$this->CustomFilter->CustomFiltersUser->Behaviors->attach('Containable');
			$findOpt = array(
				'fields'=>array('custom_filter_id','custom_filter_id'),
				'conditions'=>array(
					'CustomFiltersUser.user_id' => $this->controller->user['User']['id'],
					'CustomFiltersUser.locked' => 1,
					'CustomFilter.model' => $model
				),
				'contain' => array('CustomFilter'),
				'recursive' => 0,
			);
			$lockedFilters = $this->CustomFilter->CustomFiltersUser->find('list',$findOpt);
			$toLock = array_diff($filterIds,$lockedFilters);
			$toUnlock = array_diff($lockedFilters,$filterIds);
			if(!empty($toUnlock)){
			
				$this->CustomFilter->CustomFiltersUser->updateAll(
					array('locked' => 0),
					array(
						'CustomFiltersUser.custom_filter_id' => $toUnlock
					)
				);
			}
			if(!empty($toLock)){
				foreach($toLock as $id){
					$data = array('CustomFiltersUser'=>array(
						'locked' => 1,
						'user_id'=> $this->controller->user['User']['id'],
					));
					$this->CustomFilter->saveMyCustomFilter($id,$data);
				}
			}
		}
	}
	
	function getCurentFilterIds($model = null){
		$filterIds = array();
		if(!empty($this->controller->params['named']['filters'])){
			if($this->controller->params['named']['filters'] == 'none'){
				$filterIds = array();
			}else{
				$filterIds = explode(',',$this->controller->params['named']['filters']);
			}
		}elseif($this->controller->user){
			$findOpt = array(
				'fields'=>array('custom_filter_id','custom_filter_id'),
				'conditions'=>array(
					'CustomFiltersUser.user_id' => $this->controller->user['User']['id'],
					'CustomFiltersUser.locked' => 1
				),
				'recursive' => -1
			);
			if($model && $model !== 'all'){
				$findOpt['contain'] = array('CustomFilter');
				$findOpt['conditions']['CustomFilter.model'] = $model;
			}
			$lockedFilters = $this->CustomFilter->CustomFiltersUser->find('list',$findOpt);
			
			$filterIds = array_merge($filterIds,$lockedFilters);
		}
		
		return $filterIds;
	}
	
	function applyFilters($findOpt=array(),$model=null,$filterIds = null){
		if(is_null($model)){
			reset($this->settings['models']);
			$model = key($this->settings['models']);
		}
		if(is_null($filterIds)){
			$filterIds = $this->getCurentFilterIds();
		}
		if(empty($filterIds)) return $findOpt;
		
		$ffind=array(
			'fields'=> array(
				'CustomFilter.id','CustomFilter.model','CustomFilter.sql','CustomFilter.joins','CustomFilter.req_group',
				'CustomFilterGroup.id','CustomFilterGroup.or'
			),
			'conditions'=>array(
				'CustomFilter.id'=>$filterIds
			),
			'recursive'=>0
		);
		if($model && $model !== 'all'){
			$ffind['conditions']['CustomFilter.model'] = $model;
		}
		$filters = $this->CustomFilter->find('all',$ffind);
		
		if(empty($filters)) return $findOpt;
		
		//debug($filters);
		$byGroup = array();
		foreach($filters as $filter){
			$g = $filter['CustomFilterGroup']['id'] .'=>'. $filter['CustomFilter']['model'];
			$byGroup[$g]['CustomFilterGroup'] = $filter['CustomFilterGroup'];
			$byGroup[$g]['CustomFilter'][] = $filter['CustomFilter'];
			$byGroup[$g]['model'] = $filter['CustomFilter']['model'];
			
			if(!empty($filter['CustomFilter']['joins'])){
				foreach($filter['CustomFilter']['joins'] as $join){
					if($model === 'all'){
						$findOpt[$filter['CustomFilter']['model']]['joins'][] = $join;
					}else{
						$findOpt['joins'][] = $join;
					}
				}
			}
			
			if(!empty($filter['CustomFilter']['req_group'])){
				if($model === 'all'){
					$findOpt[$filter['CustomFilter']['model']]['group'][] = $filter['CustomFilter']['model'].'.id';
				}else{
					$findOpt['group'][] = $filter['CustomFilter']['model'].'.id';
				}
			}
			
		}
		foreach($byGroup as $group){
			$cond = array();
			foreach($group['CustomFilter'] as $filter){
				$cond[] = $filter['sql'];
			}
			if(count($cond) == 1){
				$cond = $cond[0];
			}elseif($group['CustomFilterGroup']['or']){
				$cond = array('or'=>$cond);
			}
			
			if($model === 'all'){
				$findOpt[$group['model']]['conditions'][] = $cond;
			}else{
				$findOpt['conditions'][] = $cond;
			}
		}
		//debug($findOpt);
		return $findOpt;
	}
	
	function getFilter($options){
		return $this->CustomFilter->getFilter($options);
	}
}
?>