<?php
/**
 * Copyright (c) 2013 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 *
 */

require_once('models/common/common_image.php');
 
class client_customer_image extends common_image {

	/**
	 * create table sql
	 */
	 
	private function getCreateTableSql() {
	
		$sql = "
CREATE TABLE client_customer_image (
    id serial NOT NULL PRIMARY KEY,
    src character varying(255),
    role character varying(255),
    node_id integer NOT NULL REFERENCES common_node ON UPDATE CASCADE ON DELETE CASCADE,
    title character varying(255),
    description text,
    priority integer DEFAULT 0 NOT NULL,
    modified timestamp(0) without time zone,
    author integer,
    content text,
    other_data text,
    link_to_node_id integer
);
		";
		
		return $sql;
	}
	
}