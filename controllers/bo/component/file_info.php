<?php
/**
 * Copyright (c) 2008-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 *
 */

require_once('controllers/bo/component/file.php');

class Onxshop_Controller_Bo_Component_File_Info extends Onxshop_Controller_Bo_Component_File {

	/**
	 * main action
	 */
	 
	public function mainAction() {
	
		require_once('models/common/common_file.php');
		$File = new common_file();
		
		if ($this->GET['file_path_encoded']) {
			$file_path = $File->decode_file_path($this->GET['file_path_encoded']);
			$info = $File->getFileInfo($file_path, true);
			$this->tpl->assign("ITEM", $info);
		}

		return true;
	}
}
