<?php
/** 
 * Copyright (c) 2005-2013 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 */

/**
 * find active directory
 */

$dir = str_replace($_SERVER["SCRIPT_NAME"], "", $_SERVER["SCRIPT_FILENAME"]);

/** 
 * get input variables
 */
 
$width = $_GET['width'];
$image_file = $_GET['image'];
if (array_key_exists('height', $_GET) && is_numeric($_GET['height'])) $height = $_GET['height'];
else $height = '';

/**
 * include configuration
 */
 
require_once("$dir/../conf/global.php");

/**
 * Set include paths
 */
 
set_include_path(get_include_path() . PATH_SEPARATOR . ONXSHOP_DIR);

require_once(ONXSHOP_DIR . "lib/onxshop.functions.php");
require_once(ONXSHOP_DIR . "lib/onxshop.model.php");

/**
 * get common_image configuration
 */

$GLOBALS['onxshop_conf'] = array();
require_once(ONXSHOP_DIR . "models/common/common_image.php");
$image_configuration = common_image::initConfiguration();

/**
 * check requested width
 */

if ($width > $image_configuration['width_max']) {

	$image_file = null;
	
} else {

	if ($width < $image_configuration['thumbnail_width_min']) $image_file = null;
	if ($width > $image_configuration['thumbnail_width_max']) $image_file = null;
	if ($width%$image_configuration['thumbnail_step'] > 0) $image_file = null;
	if (!is_readable(ONXSHOP_PROJECT_DIR . $image_file)) $image_file = null;

	if ($image_file) {

		/**
		 * get content type
		 */
		 
		$mime_type = local_exec("file -bi " . escapeshellarg(ONXSHOP_PROJECT_DIR . $image_file));
		$mime_type = trim($mime_type);
		
		/**
		 * check what to display
		 */
		 
		if (preg_match("/image/", $mime_type)) {
			//if image, process it
		} else if ($mime_type == 'application/pdf') {
			$image_file = "public_html/share/images/mimetype/pdf.png";	
		} else if ($mime_type == 'application/msword') {
			$image_file = "public_html/share/images/mimetype/document.png";	
		} else if ($mime_type == 'application/vnd.ms-excel') {
			$image_file = "public_html/share/images/mimetype/spreadsheet.png";
		} else {
			$image_file = "public_html/share/images/mimetype/ascii.png";	
		}
		
		/**
		 * try
		 */
		
		if ($thumbnail = common_image::resize($image_file, $width, $height)) $image_file = $thumbnail;
		
	} else {

		$image_file = null;
	}
}


/**
 * send image to the client through our image get script
 */
 
$_GET['image'] = $image_file;
include(ONXSHOP_DIR . "share/image_get.php");

