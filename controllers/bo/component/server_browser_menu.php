<?php
/**
 * Menu of server files
 *
 * Copyright (c) 2006-2013 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 *
 */
 
require_once('controllers/component/menu_js.php');

class Onxshop_Controller_Bo_Component_Server_Browser_Menu extends Onxshop_Controller_Component_Menu_Js {
	
	/**
	 * get list
	 */
	 
	public function getList($publish = 1) {
			
		/**
		 * set prefix
		 */
		 	
		switch ($this->GET['scope']) {
		
			case 'onxshop':
				$directories = array(ONXSHOP_DIR);
			break;
			case 'all':
				$directories = array(ONXSHOP_DIR, ONXSHOP_PROJECT_DIR);
			break;
			case 'project':
			default:
				$directories = array(ONXSHOP_PROJECT_DIR);
			break;
		
		}
		
		/**
		 * get list
		 */
		
		$list = $this->getListForDirectories($directories);
		
		/**
		 * trim extension
		 */
		 
		if ($this->GET['trim_extension']) {
			foreach ($list as $k=>$item) {
				$list[$k]['name'] = preg_replace('/\.html$/', '', $list[$k]['name']);
				$list[$k]['name'] = preg_replace('/\.php$/', '', $list[$k]['name']);
				$list[$k]['id'] = preg_replace('/\.html$/', '', $list[$k]['id']);
				$list[$k]['id'] = preg_replace('/\.php$/', '', $list[$k]['id']);
				$list[$k]['parent'] = preg_replace('/\.html$/', '', $list[$k]['parent']);
				$list[$k]['parent'] = preg_replace('/\.php$/', '', $list[$k]['parent']);
			}
		}
		
		return $list;
	}
	
	/**
	 * getListForDirectories
	 */
	
	public function getListForDirectories($directories) {
		
		if (!is_array($directories)) return false;
		
		/**
		 * pass find param
		 */
		
		switch ($this->GET['type']) {
			case 'd':
				$find_param = '-type d';
			break;
			default:
				$find_param = '';
			break;
		}  
		
		/**
		 * get list
		 */
		 
		require_once('models/common/common_file.php');
		$File = new common_file();

		$list = array();
		
		foreach ($directories as $directory_prefix) {
		
			$directory = $directory_prefix . $this->GET['directory'];
			$list_single = $File->getTree($directory, $find_param);

			$list = array_merge($list, $list_single);
			
		}
		
		if (is_array($list)) return $list;
		else return false;
		
	}
}

