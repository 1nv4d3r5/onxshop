<?php
/**
 * Copyright (c) 2009-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 */

require_once('models/ecommerce/ecommerce_store.php');
require_once('models/ecommerce/ecommerce_store_taxonomy.php');
require_once('models/common/common_node.php');
require_once('models/common/common_node_taxonomy.php');
require_once('models/common/common_uri_mapping.php');
require_once('models/client/client_customer.php');

class Onxshop_Controller_Component_Store_Locator extends Onxshop_Controller {

	/**
	 * main action
	 */
	public function mainAction()
	{
		// init URI mapping model
		$Mapping = new common_uri_mapping();

		// get selected store for detail
		$node_id = (int) $this->GET['node_id'];

		// load stores, store pages and related categories
		$store_pages = $this->getStorePages();
		$stores = $this->getAllStores();
		$categories = $this->getAllStoreTaxonomyIds();
		$selected_store = $this->getStoreAssociatedToNode($node_id);
		$page_categories = $this->getPageTaxonomyIds($node_id);

		// process request to save store as my own store
		if ($this->GET['set_home_store'] == 'true' && $selected_store['id'] > 0) {
			$this->updateCustomersHomeStore($selected_store['id']);
			msg("You home store has been updated.");
			onxshopGoTo("page/$node_id");
		}

		// init map bounds
		$bounds['latitude']['max'] = -9999;
		$bounds['latitude']['min'] = 9999;
		$bounds['longitude']['max'] = -9999;
		$bounds['longitude']['min'] = 9999;

		// display pins
		foreach ($stores as $store) {

			if ($store['latitude'] != 0 && $store['longitude'] != 0) {

				// find page and url
				$page = $store_pages[$store['id']];
				$store['url'] = $Mapping->stringToSeoUrl("/page/{$page['id']}");
				$store['icon'] = $store['id'] == $selected_store['id'] ? 'false' : 'true';
				$store['open'] = $store['id'] == $selected_store['id'] ? 'true' : 'false';

				// adjust bounds (by province/county)
				if ($node_id == 1345 || array_intersect($page_categories, $categories[$store['id']])) {
					
					if ($store['latitude'] > $bounds['latitude']['max']) $bounds['latitude']['max'] = $store['latitude'];
					if ($store['latitude'] < $bounds['latitude']['min']) $bounds['latitude']['min'] = $store['latitude'];
					if ($store['longitude'] > $bounds['longitude']['max']) $bounds['longitude']['max'] = $store['longitude'];
					if ($store['longitude'] < $bounds['longitude']['min']) $bounds['longitude']['min'] = $store['longitude'];
					$store['icon'] = 'false';
				}

				// parse item
				$this->tpl->assign("STORE", $store);
				$this->tpl->parse("content.store_marker");

			}
		}

		// center map to ...
		if ($selected_store) {

			// ... to a selected store
			$map['latitude'] = $selected_store['latitude'] + 0.004;
			$map['longitude'] = $selected_store['longitude'];

		} else {

			// ... to bounds of a selected region (province/county)
			if ($bounds['latitude']['min'] != 9999) {
				$this->tpl->assign("BOUNDS", $bounds);
				$this->tpl->parse("content.fit_to_bounds");
			}
			$map['latitude'] = 53.344189;
			$map['longitude'] = -6.264478;
		}

		$this->tpl->assign("MAP", $map);

		return true;
	}



	/**
	 * Returns array of all store pages. Store id is used as array index.
	 * 
	 * @return Array
	 */
	protected function getStorePages()
	{

		$Node = new common_node();

		$pages_raw = $Node->listing("node_group = 'page' AND node_controller = 'store' AND content ~ '[0-9]+'");

		$pages = array();

		foreach ($pages_raw as $page) {
			$store_id = (int) $page['content'];
			$pages[$store_id] = $page;
		}

		return $pages;
	}


	/**
	 * Returns array of all published stores in the database
	 * 
	 * @return Array
	 */
	protected function getAllStores()
	{
		$Store = new ecommerce_store();
		return $Store->listing("publish = 1");
	}



	/**
	 * Returns two dimensional arrays of store categories. 
	 * Result array has the following structure:
	 *
	 * array(
	 *    store_id => array( taxonomy_id, taxonomy_id, ...)
	 *    store_id => array( taxonomy_id, taxonomy_id, ...)
	 *    store_id => array( taxonomy_id, taxonomy_id, ...)
	 *    ...
	 * )
	 *
	 * I.e. list of categories associated to the store can
	 * be accesed using $categories[$store_id]
	 * 
	 * @return Array
	 */
	protected function getAllStoreTaxonomyIds()
	{
		$Store_Taxonomy = new ecommerce_store_taxonomy();

		$categories_raw = $Store_Taxonomy->listing();

		$categories = array();

		// allow access by store_id
		foreach ($categories_raw as $category) {
			$categories[$category['node_id']][] = $category['taxonomy_tree_id'];
		}

		return $categories;
	}



	/**
	 * Returns store associated to given node
	 * 
	 * @param  int    $node_id Store page node_id
	 * @return Array
	 */
	protected function getStoreAssociatedToNode($node_id)
	{
		$Store = new ecommerce_store();
		return $Store->findStoreByNode($node_id);
	}



	/**
	 * Return array of taxonomy_ids associated to given node
	 * 
	 * @param  int    $node_id Node id
	 * @return Array
	 */
	protected function getPageTaxonomyIds($node_id)
	{
		$Node_Taxonomy = new common_node_taxonomy();

		$page_categories_raw = $Node_Taxonomy->listing("node_id = $node_id");

		$page_categories = array();

		foreach ($page_categories_raw as $category) {
			$page_categories[] = $category['taxonomy_tree_id'];
		}

		return $page_categories;
	}



	/**
	 * Update customer's other_data to include given home store_id
	 * 
	 * @param  int $store_id Store id
	 */
	protected function updateCustomersHomeStore($store_id)
	{
		$Customer = new client_customer();

		// update other_data
		$other_data = unserialize($_SESSION['client']['customer']['other_data']);
		$other_data['home_store_id'] = $store_id;
		$other_data = serialize($other_data);

		$_SESSION['client']['customer']['other_data'] = $other_data;

		$Customer->update(array(
			'id' => $_SESSION['client']['customer']['id'],
			'other_data' => $other_data
		));
	}

}

