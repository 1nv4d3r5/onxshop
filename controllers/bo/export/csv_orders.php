<?php
/** 
 * Copyright (c) 2008-2012 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 * 
 */

require_once('controllers/bo/export/csv.php');

class Onxshop_Controller_Bo_Export_CSV_Orders extends Onxshop_Controller_Bo_Export_CSV {

	/**
	 * main action
	 */
	 
	public function mainAction() {
		
		set_time_limit(0);
		
		require_once('models/ecommerce/ecommerce_order.php');
		
		$Order = new ecommerce_order();
		
		/**
		 * Get the list
		 */
		
		$records = $Order->getOrderListForExport($_SESSION['order-list-filter'], $this->GET['inlude_products'] == 1);

		if (is_array($records)) {
		
				/**
				 * parse records
				 */
				$header = 0;
				
				foreach ($records as $record) {
				
					/**
					 * Create header
					 */
					if ($header == 0) {
					
						foreach ($record as $key=>$val) {
						
							$column['name'] = $key;
							$this->tpl->assign('COLUMN', $column);
							$this->tpl->parse('content.th');
						}
						
						$header = 1;
					}
		        
					foreach ($record as $key=>$val) {
						
						if (!is_numeric($val)) {
						
							$val = addslashes($val);
							$val = '"' . $val . '"';
							$val = preg_replace("/[\n\r]/", '', $val);
						
						}
						
						$this->tpl->assign('value', $val);
						$this->tpl->parse('content.item.attribute');
					}
			
					$this->tpl->parse('content.item');
				}
		
		    //set the headers for the output
			$this->sendCSVHeaders('orders' . ($this->GET['inlude_products'] == 1 ? '-products' : ''));
		
		} else {
		
			echo "no records"; exit;
		
		}

		return true;
	}
}
