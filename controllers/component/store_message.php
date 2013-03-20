<?php
/** 
 * Copyright (c) 2013 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 * 
 */

require_once('models/ecommerce/ecommerce_store.php');
require_once('models/common/common_node.php');

class Onxshop_Controller_Component_Store_Message extends Onxshop_Controller {

	/**
	 * main action
	 */
	 
	public function mainAction() {
	
		if ($_SESSION['client']['customer']['id'] > 0) {

			$other_data = unserialize($_SESSION['client']['customer']['other_data']);
			$store_id = (int) $other_data['home_store_id']; 

			if ($store_id > 0) {

				$Store = new ecommerce_store();
				$store = $Store->detail($store_id);
				$store_page = $Store->getStoreHomepage($store_id);

				$this->tpl->assign("STORE_PAGE", $store_page);
				$this->tpl->assign("STORE", $store);
				$this->tpl->parse('content.authorised_selected');
			}
			else {
				$this->tpl->parse('content.authorised_not_selected');
			}
			
		} else {
		
			$this->tpl->parse('content.anonymouse');
		
		}

		return true;
		
	}



}
