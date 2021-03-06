<?php
/**
 * Copyright (c) 2013 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 * 
 */

require_once('models/ecommerce/ecommerce_recipe.php');

class Onxshop_Controller_Component_Ecommerce_Recipe_List extends Onxshop_Controller {

	/**
	 * main action (only a router in this case)
	 */

	public function mainAction()
	{
		// init models
		$Recipe = new ecommerce_recipe();

		// get taxonomy ids
		$taxonomy_ids = explode(",", $this->GET['taxonomy_tree_id']);

		// validate input
		if (!is_array($taxonomy_ids)) return false;
		foreach ($taxonomy_ids as $taxonomy_id) 
			if (!is_numeric($taxonomy_id)) return false;

		// is there a limit?
		if  (is_numeric($this->GET['limit_from'])) $from = $this->GET['limit_from'];
		else $from = 0;
		if (is_numeric($this->GET['limit_per_page'])) $per_page = $this->GET['limit_per_page'];
		else $per_page = 100;

		$limit = "$from,$per_page";
		$order = $this->prepareSorting($this->GET['sort']);

		$list = $Recipe->getRecipeListForTaxonomy($taxonomy_ids, $order, $limit);

		$this->parseItems($list);

		return true;
	}

	protected function prepareSorting($sort)
	{
		if ($sort['by'] && in_array($sort['by'], array('title', 'created', 'priority', 'share_counter'))) {
			$_SESSION['recipe_list-sort-by'] = $sort['by'];
			$_SESSION['use_page_cache'] = false;
		} else if (!$_SESSION['recipe_list-sort-by']) $_SESSION['recipe_list-sort-by'] = 'created';
		
		if ($sort['direction'] && in_array($sort['direction'], array('DESC', 'ASC'))) {
			$_SESSION['recipe_list-sort-direction'] = $sort['direction'];
			$_SESSION['use_page_cache'] = false;
		} else if (!$_SESSION['recipe_list-sort-direction']) $_SESSION['recipe_list-sort-direction'] = 'DESC';

		return $_SESSION['recipe_list-sort-by'] . " " . $_SESSION['recipe_list-sort-direction'];
	}

	/**
	 * Parse recipe list items
	 */
	public function parseItems(&$list)
	{
		foreach ($list as $i => $item) {
			
			$this->tpl->assign("ITEM", $item);
			if ($item['review']['count'] > 0) {
				
				$rating = round($item['review']['rating']);
				$_Onxshop_Request = new Onxshop_Request("component/rating_stars~rating={$rating}~");
				$this->tpl->assign('RATING_STARS', $_Onxshop_Request->getContent());
				if ($item['review']['count'] == 1) $this->tpl->assign('REVIEWS', 'Review');
				else $this->tpl->assign('REVIEWS', 'Reviews');
				
				$this->tpl->parse('content.item.reviews');
			}
			$this->tpl->parse("content.item");
		}
	}

}
