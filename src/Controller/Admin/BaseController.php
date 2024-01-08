<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected $list = array();
	protected $jwtEncoder;
	protected $token;

	public function __construct(){}

	public function getList($default=array()){
		$this->initList($default);

		return $this->listQuery(true);
	}

/* 
	name: initList
	description: Sets up the paginated list query
*/     
	private function initList($default=array()){
		$this->list = array();
		$this->list['default'] = array(
			'view' => 'table', 
			'p_size' => 12, 
			'page' => 1,
			'order' => 'ASC', 
			'orderby' => 'id',
			'total' => 0,
			'n_pages' => 1,
			'offset' => 0,
			'filter' => array(), 
			'filter_fields' => array()
		);
		if(!empty($default)){
			foreach($default as $key => $value)
				$this->list['default'][$key] = $value;
		}
		$this->list = $this->list['default'];
		$this->list['session_key'] = get_class($this).'\ListData';
		
		$this->updateList();
		
	}

/* 
	name: updateList
	description: Updates the paginated list query
*/ 	
	private function updateList(){
		
		//If no parameters has been set, it gets the parameters saved in session
		if(empty($_GET)&&!empty($this->get('session')->get($this->list['session_key']))){
			$this->list = $this->get('session')->get( $this->list['session_key'] );
		}else{
			$this->updateFilter();
			$this->updateListParam('p_size', true);
			$this->updateListParam('page', true);
			$this->updateListParam('view');
			$this->updateListParam('orderby');
			$this->updateListParam('order');
			if(empty($this->list['order'])||strtoupper($this->list['order'])!='ASC') 
				$this->list['order'] = 'DESC';
		}
		
		
		//Get all items without pagination
		$all = $this->listQuery(false);
		
		$this->list['total'] = count($all);
		$this->list['n_pages'] = ceil($this->list['total'] / $this->list['p_size']);
		
		//Get page and offset
		if($this->list['page']<1) $this->list['page'] = 1;
		if($this->list['page']>$this->list['n_pages']) $this->list['page'] = $this->list['n_pages'];
		$this->list['offset'] = $this->list['p_size']*($this->list['page']-1);
		if($this->list['offset']<0) $this->list['offset'] = 0;
		if($this->list['offset']>$this->list['total']) $this->list['offset'] = $this->list['total'];

		$this->saveList();
	}

/* 
	name: saveList
	description: Stores in the session data the configuration of the paginated list query
*/	
	private function saveList(){
		$this->get('session')->set($this->list['session_key'], $this->list);
	}

/* 
	name: updateFilter
	description: Updates the filter of the paginated list query
*/	
	private function updateFilter(){
		
		if(!empty($_GET['filter'])&&is_array($_GET['filter'])){
			foreach($_GET['filter'] as $key => $value){
				if(in_array($key, $this->list['filter_fields']))
					$this->list['filter'][$key] = $value;
			}
		}

		if(!empty($_GET['search']))
			$this->list['filter']['search'] = $_GET['search'];
	}

/* 
	name: updateListParam
	description: Updates a parameter of the paginated list query
*/	
	private function updateListParam($key, $number=false){
		
		if(!empty($_GET[$key])){ 
			$this->list[$key] = $_GET[$key];
		}else if(!empty($this->list['default'][$key])){
			$this->list[$key] = $this->list['default'][$key];
		}

		if($number && !empty($this->list[$key])){
			$this->list[$key] = intval($this->list[$key]);
		}
	}

/* 
	name: listQuery
	description: Method that the child classes must override to perform the query
*/		
	public function listQuery($pagination=true): Array{
		return array();
	}
}
