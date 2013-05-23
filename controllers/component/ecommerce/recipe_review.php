<?php
/** 
 * Copyright (c) 2013 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 */

require_once('controllers/component/ecommerce/product_review.php');

class Onxshop_Controller_Component_Ecommerce_Recipe_Review extends Onxshop_Controller_Component_Ecommerce_Product_Review {
	
	/**
	 * initialize comment
	 */
	 
	public function initializeComment() {
	
		require_once('models/ecommerce/ecommerce_recipe_review.php');
		return new ecommerce_recipe_review();
		
	}
	
	/**
	 * custom comment action
	 */
	 
	public function customCommentAction($data, $options) {
	
		$_Onxshop_Request = new Onxshop_Request("component/ecommerce/recipe_review_list~node_id={$data['node_id']}:allow_anonymouse_submit={$options['allow_anonymouse_submit']}~");
		$this->tpl->assign('REVIEW_LIST', $_Onxshop_Request->getContent());
		
		$_Onxshop_Request = new Onxshop_Request("component/ecommerce/recipe_review_add~node_id={$data['node_id']}:allow_anonymouse_submit={$options['allow_anonymouse_submit']}~");
		$this->tpl->assign('REVIEW_ADD', $_Onxshop_Request->getContent());
		
	}
	
		
	/**
	 * check data
	 */
	 
	public function checkData($data) {
	
		if (trim($data['title']) == '' || trim($data['author_name']) == '' || trim($data['author_email']) == '' || trim($data['title']) == '' || $data['rating'] == 0) return false;
		else return true;
	}
}
