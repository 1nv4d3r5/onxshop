<?php
/**
 * Backoffice product list filter
 *
 * Copyright (c) 2008-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 */

class Onxshop_Controller_Bo_Component_Ecommerce_Store_List_Filter extends Onxshop_Controller {
	
	/**
	 * main action
	 */
	 
	public function mainAction() {
		
		if (isset($_POST['recipe-list-filter'])) $_SESSION['recipe-list-filter'] = $_POST['recipe-list-filter'];

		return true;
	}
}
