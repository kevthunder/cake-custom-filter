<?php
class CustomFiltersController extends CustomFilterAppController {

	var $name = 'CustomFilters';
	var $components = array('RequestHandler');
	var $helpers = array('CustomFilter.Ressources','CustomFilter.CustomFilter');

	function admin_index() {
		$q = null;
		if(isset($this->params['named']['q']) && strlen(trim($this->params['named']['q'])) > 0) {
			$q = $this->params['named']['q'];
		} elseif(isset($this->data['CustomFilter']['q']) && strlen(trim($this->data['CustomFilter']['q'])) > 0) {
			$q = $this->data['CustomFilter']['q'];
			$this->params['named']['q'] = $q;
		}
		
		
		if($q !== null) {
			$this->paginate['conditions']['OR'] = array('CustomFilter.title LIKE' => '%'.$q.'%',
														'CustomFilter.desc LIKE' => '%'.$q.'%',
														'CustomFilter.model LIKE' => '%'.$q.'%',
														'CustomFilter.sql LIKE' => '%'.$q.'%');
		}

		$this->CustomFilter->recursive = 0;
		$this->set('customFilters', $this->paginate());
	}

	function admin_add() {
		$submit = !empty($this->data);
		if ($submit) {
			unset($this->data['CustomFilterCond']['%%i%%']);
			if($this->user){
				$this->data['CustomFiltersUser']['user_id'] = $this->user['User']['id'];
			}
			$this->CustomFilter->create();
			$this->data['CustomFilter']['active'] = 1;
			$this->CustomFilter->data = array();
			if ($this->CustomFilter->save($this->data)) {
				if(!empty($this->params['isAjax'])){
					echo $this->CustomFilter->id;
					exit;
				}else{
					$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'custom filter'));
					$this->redirect(array('action' => 'index'));
				}
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'custom filter'));
			}
		}else{
			$this->data['CustomFilter']['active'] = 1;
			if(!empty($this->params['named']['model'])){
				$this->data['CustomFilter']['model'] = $this->params['named']['model'];
			}
		}
		if($Model = $this->CustomFilter->getFilteredModel($this->data)){
			$settings = $Model->getfilterSettings();
			if($settings['save'] === 'default' && !$submit){
				$this->data['CustomFilter']['save'] = 1;
				$this->data['CustomFilter']['public'] = 1;
			}
		
			$this->set('fieldsChoices',$Model->filterableFields());
			
			$this->set('settings',$settings);
			$this->set('typesData',$this->_filter_type($this->data,$Model));
		}
	}
	
	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'custom filter'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			unset($this->data['CustomFilterCond']['%%i%%']);
			if($this->user){
				$this->data['CustomFiltersUser']['user_id'] = $this->user['User']['id'];
			}
			if ($this->CustomFilter->save($this->data)) {
				if(!empty($this->params['isAjax'])){
					echo $this->CustomFilter->id;
					exit;
				}else{
					$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'custom filter'));
					$this->redirect(array('action' => 'index'));
				}
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'custom filter'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->CustomFilter->read(null, $id);
		}
		if($Model = $this->CustomFilter->getFilteredModel($this->data)){
			$this->set('fieldsChoices',$Model->filterableFields());
			$this->set('settings',$Model->getfilterSettings());
			$this->set('typesData',$this->_filter_type($this->data,$Model));
		}
	}
	
	function _filter_type($filter,$Model=null){
		if(!$Model) $Model = $this->CustomFilter->getFilteredModel($filter);
		if (!$Model) {
			return null;
		}
		
		$data = array();
		if(!empty($filter['CustomFilterCond'])){
			if(!Set::numeric(array_keys($filter['CustomFilterCond']))) $filter['CustomFilterCond'] = array($filter['CustomFilterCond']);
			foreach($filter['CustomFilterCond'] as $pos => $cond){
				$cond = array_merge($filter,array('CustomFilterCond'=>$cond));
				if(!empty($cond['CustomFilterCond']['field'])){
					$FilterType = $this->CustomFilter->CustomFilterCond->getType($cond,$Model);
					if(!empty($FilterType)){
						$elem = $FilterType->getElementName();
						$c = array(
							'opt' => $FilterType->filter(),
							'element' => $elem['elem'],
							'type' => $FilterType->name,
						);
						if($elem['plugin']) $c['opt']['plugin']= $elem['plugin'];
						if($c['element']){
							$data[$pos] = $c;
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	function admin_filter_type($model=null,$field=null,$order=0){
		$this->autoRender = false;
		if (empty($model) || empty($field)) {
			return $this->cakeError('error404');
		}
		
		$filter = array('CustomFilter'=>array('model'=>$model),'CustomFilterCond'=>array('field'=>$field));
		
		$data = $this->_filter_type($filter);
		if($data){
			$data = reset($data);
			$data['order'] = $order;
		
			$this->set($data);
			$this->render();
			return;
		}
		
		$this->render(false);
	}

	function admin_lock($id = null,$lock=1) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'custom filter'));
			$this->redirect($this->referer(array('action' => 'index'),true));
		}
		$data = array('CustomFiltersUser'=>array(
			'locked' => $lock,
		));
		if($this->user){
			$data['CustomFiltersUser']['user_id'] = $this->user['User']['id'];
		}
		if ($this->CustomFilter->saveMyCustomFilter($id,$data)) {
			$this->Session->setFlash(sprintf(__('%s Locked', true), 'Custom filter'));
			$this->redirect($this->referer(array('action' => 'index'),true));
		}
		$this->Session->setFlash(sprintf(__('%s could not be locked', true), 'Custom filter'));
		$this->redirect($this->referer(array('action' => 'index'),true));
		
	}
	
	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'custom filter'));
			$this->redirect($this->referer(array('action' => 'index'),true));
		}
		if ($this->CustomFilter->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), 'Custom filter'));
			$this->redirect($this->referer(array('action' => 'index'),true));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Custom filter'));
		$this->redirect($this->referer(array('action' => 'index'),true));
	}
	
}
?>