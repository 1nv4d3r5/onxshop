<?php
/**
 * class ecommerce_product_type
 * link to document with vat rates
 *
 * Copyright (c) 2009-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 *
 */
 
class ecommerce_product_type extends Onxshop_Model {

	/**
	 * @access private
	 */
	var $id;
	/**
	 * @access private
	 */
	var $name;
	/**
	 * @access private
	 */
	var $vat;
	
	var $publish;


	var $_metaData = array(
		'id'=>array('label' => '', 'validation'=>'int', 'required'=>true), 
		'name'=>array('label' => '', 'validation'=>'string', 'required'=>true),
		'vat'=>array('label' => '', 'validation'=>'numeric', 'required'=>true),
		'publish'=>array('label' => '', 'validation'=>'int', 'required'=>false)
	);

	/**
	 * create table sql
	 */
	 
	private function getCreateTableSql() {
	
		$sql = "
CREATE TABLE ecommerce_product_type (
    id serial NOT NULL PRIMARY KEY,
    name character varying(255),
    vat numeric DEFAULT 0 NOT NULL,
	publish integer DEFAULT 1 NOT NULL
);
		";
		
		return $sql;
	}
	
	/**
	 * init configuration
	 */
	 
	static function initConfiguration() {
		if (array_key_exists('ecommerce_price', $GLOBALS['onxshop_conf'])) $conf = $GLOBALS['onxshop_conf']['ecommerce_price'];
		else $conf = array();
		
		$conf['default_id'] = 9;
		
		return $conf;
	}
}