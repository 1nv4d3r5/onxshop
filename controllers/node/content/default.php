<?php
/**
 * Copyright (c) 2009-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 * 
 */

require_once('controllers/node/default.php');

class Onxshop_Controller_Node_Content_Default extends Onxshop_Controller_Node_Default {

	/**
	 * main action
	 */
	 
	public function mainAction() {
		//$this->processContainers();
		if ($this->processContent()) return true;
		else return false;
	}
	
	/**
	 * process content
	 */
	 
	public function processContent() {
		
		$node_id = $this->GET['id'];
		
		require_once('models/common/common_node.php');
		
		$Node = new common_node();
		
		$node_data = $Node->nodeDetail($node_id);
		
		if ($node_data['page_title'] == '') {
			$node_data['page_title'] = $node_data['title'];
		}
		
		if (!isset($node_data['display_title'])) $node_data['display_title'] = $GLOBALS['onxshop_conf']['global']['display_title'];
		
		$this->tpl->assign("NODE", $node_data);
		
		/**
		 * display title
		 */
		 
		$this->displayTitle($node_data);


		return true;
	}
	
	/**
	 * display title
	 */
	 
	public function displayTitle($node_data) {
 
		if ($node_data['display_title'])  {
			if ($node_data['link_to_node_id'] > 0) $this->tpl->parse('content.title_link');
			else $this->tpl->parse('content.title');
		}
		
	}
}
