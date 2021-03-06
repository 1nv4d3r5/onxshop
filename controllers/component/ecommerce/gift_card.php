<?php
/**
 * Gift message
 *
 * Copyright (c) 2009-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 * 
 */

class Onxshop_Controller_Component_Ecommerce_Gift_Card extends Onxshop_Controller {

	/**
	 * main action
	 */
	 
	public function mainAction() {
	
		if (!is_numeric($this->GET['order_id'])) return false;

		require_once('models/ecommerce/ecommerce_order.php');
		
		$Order = new ecommerce_order();
		
		$order_detail = $Order->getOrder($this->GET['order_id']);

		//check owner
		if ($order_data['basket']['customer_id'] !== $_SESSION['client']['customer']['id'] &&  $_SESSION['authentication']['logon'] == 0) {
			msg('gift_card:unauthorized access to view order detail');
			return false;
		} else {
			$this->tpl->assign('ORDER', $order_detail);
		}
		
		return true;
	}
}
