<?php
/**
 *
 * Copyright (c) 2013 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 *
 */
 
require_once('models/common/common_node_taxonomy.php');

class client_customer_taxonomy extends common_node_taxonomy {

	/**
	 * NOT NULL REFERENCES client_customer ON UPDATE CASCADE ON DELETE CASCADE
	 * @access private
	 */
	var $node_id;

	/**
	 * create table sql
	 */
	 
	private function getCreateTableSql() {
	
		$sql = "
CREATE TABLE client_customer_taxonomy (
    id serial PRIMARY KEY NOT NULL,
    node_id integer NOT NULL REFERENCES client_customer(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    taxonomy_tree_id integer NOT NULL REFERENCES common_taxonomy_tree(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    UNIQUE (node_id, taxonomy_tree_id)
);

CREATE INDEX client_customer_taxonomy_node_id_key1 ON client_customer_taxonomy USING btree (node_id);
CREATE INDEX client_customer_taxonomy_taxonomy_tree_id_key ON client_customer_taxonomy USING btree (taxonomy_tree_id);
		";
		
		return $sql;
	}
	
	/**
	 * init configuration
	 */
	 
	static function initConfiguration() {
		if (array_key_exists('client_customer_taxonomy', $GLOBALS['onxshop_conf'])) $conf = $GLOBALS['onxshop_conf']['client_customer_taxonomy'];
		else $conf = array();
		
		return $conf;
	}
	
	/**
	 * get relations
	 */
	
	function getRelationsToCustomer($customer_id) {
	
		if (!is_numeric($customer_id)) return false;
		
		$relations_list = $this->listing("node_id = $customer_id");
		
		foreach($relations_list as $item) {
			$relations[] = $item['taxonomy_tree_id'];
		}
		
		return $relations;
		
	}
}
