<?php
/**
 * Copyright (c) 2008-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 *
 */

class Onxshop_Controller_Bo_Node_Default extends Onxshop_Controller {

	var $name;
	var $Node;
	var $node_data;
	var $component_data;
	
	/**
	 * main action
	 */
	 
	public function mainAction() {
	
		require_once('models/common/common_node.php');
		$this->Node = new common_node();	
		$this->pre();
		if ($_POST['save']) $this->save();
		$this->detail();
		$this->post();
		$this->assign();

		return true;
	}
	

	/**
	 * save
	 *
	 */
	 
	function save() {
		
		$node_data = $_POST['node']; 

		if ($node_data['publish'] == 'on' || $node_data['publish'] == 1) $node_data['publish'] = 1;
		else $node_data['publish'] = 0;

		if ($node_data['display_title'] == 'on' || $node_data['display_title'] == 1) $node_data['display_title'] = 1;
		else $node_data['display_title'] = 0;
		
		if ($node_data['require_login'] == 'on' || $node_data['require_login'] == 1) $node_data['require_login'] = 1;
		else $node_data['require_login'] = 0;
		
		if ($node_data['require_ssl'] == 'on' || $node_data['require_ssl'] == 1) $node_data['require_ssl'] = 1;
		else $node_data['require_ssl'] = 0;
		
		if($this->Node->nodeUpdate($node_data)) {
			msg("{$node_data['node_group']} (id={$node_data['id']}) has been updated");
		} else {
			msg("Cannot update node {$node_data['node_group']} (id={$node_data['id']})", 'error');
		}
		

		//get whole detail
		$this->detail($_POST['node']['id']);
		//overwrite posted data

		$this->node_data = array_merge($this->node_data, $_POST['node']);
	}
	
	/**
	 * get node detail
	 *
	 */
	 
	function detail() {
		$this->node_data = $this->Node->nodeDetail($this->GET['id']);
	}
	
	/**
	 * assign to template
	 *
	 */
	 
	function assign() {
		//display
		if ($this->node_data['publish'] == 1) {
			$this->node_data['publish_check'] = 'checked="checked"';
		} else {
			$this->node_data['publish_check'] = '';
		}

		//display title
		if (!is_numeric($this->node_data['display_title'])) $this->node_data['display_title'] = $GLOBALS['onxshop_conf']['global']['display_title'];

		if ($this->node_data['display_title'] == 1) {
			$this->node_data['display_title_check'] = 'checked="checked"';
		} else {
			$this->node_data['display_title_check'] = '';
		}
		
		//require_login
		if ($this->node_data['require_login'] == 1) {
			$this->node_data['require_login_check'] = 'checked="checked"';
		} else {
			$this->node_data['require_login_check'] = '';
		}
		
		//require_ssl
		if ($this->node_data['require_ssl'] == 1) {
			$this->node_data['require_ssl_check'] = 'checked="checked"';
		} else {
			$this->node_data['require_ssl_check'] = '';
		}
		
		
		//display in menu
		$this->node_data["display_in_menu_select_" . $this->node_data['display_in_menu']] = "selected='selected'";

		//display permission
		if ($this->node_data['display_permission'] > 0) $this->node_data["display_permission_select_{$this->node_data['display_permission']}"] = "selected='selected'";
		else $this->node_data['display_permission_select_0'] = "selected='selected'";
		
		//display_permission_group_acl
		$this->renderDisplayPersmissionGroupAcl($this->node_data);
		

		// get the list of node types
		$Node_type = new Onxshop_Request("bo/component/node_type_menu~id=0:open={$this->node_data['node_controller']}:node_group={$this->node_data['node_group']}:expand_all=1~");
		$this->tpl->assign("NODE_TYPE", $Node_type->getContent());
		
		$this->tpl->assign('NODE', $this->node_data);

	}
	
	/**
	 * renderDisplayPersmissionGroupAcl
	 */
	 
	public function renderDisplayPersmissionGroupAcl($node_data) {
		
		require_once('models/client/client_group.php');
		$ClientGroup = new client_group();
		$client_group_list = $ClientGroup->listGroups();
		
		
		if (count($client_group_list) > 0) {
			
			foreach($client_group_list as $item) {
				
				//selected option for each group
				$selected = array();
				if (is_array($node_data['display_permission_group_acl'])) {
				
					$item_value = $node_data['display_permission_group_acl'][$item['id']];
					$selected['item_' . $item_value] = 'selected="selected"';
				
				} else {
				
					$selected['item_-1'] = 'selected="selected"';
					
				}
				
				$this->tpl->assign("SELECTED", $selected);
				
				$this->tpl->assign('GROUP', $item);
				
				$this->tpl->parse('content.display_permission_group_acl.item');
				
			}
		
			
			//selected option for Everyone
			$selected = array();
			if (is_array($node_data['display_permission_group_acl'])) {
				$item_value = $node_data['display_permission_group_acl'][0];
				$selected['item_' . $item_value] = 'selected="selected"';
			} else {
				$selected['item_-1'] = 'selected="selected"';
			}
			$this->tpl->assign("SELECTED", $selected);
			
			
		} else {
			$this->tpl->assign('DISABLE_EMPTY', 'disabled="disabled"');
			$this->tpl->parse('content.display_permission_group_acl.empty');
		}
		
		$this->tpl->parse('content.display_permission_group_acl');
	}
	
	/**
	 * hook
	 *
	 */
	 
	function pre() {
		
	}
	
	/**
	 * hook
	 *
	 */
	 
	function post() {
		
	}
}

		
