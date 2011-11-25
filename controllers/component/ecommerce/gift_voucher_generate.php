<?php
/**
 * Copyright (c) 2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 * 
 */

require_once('controllers/component/ecommerce/gift_voucher.php');

class Onxshop_Controller_Component_Ecommerce_Gift_Voucher_Generate extends Onxshop_Controller_Component_Ecommerce_Gift_Voucher {

	/**
	 * main action
	 */
	 
	public function mainAction() {
		
		if (!is_numeric($this->GET['order_id'])) {
			msg("Onxshop_Controller_Component_Ecommerce_Gift_Voucher_Generate: order_id isn't numeric");
			return false;
		}
		
		$order_id = $this->GET['order_id'];
		
		if ($gift_voucher_product_id = $this->getGiftVoucherProductId($order_id)) {
		
			/**
			 * get order detail
			 */
			 
			require_once('models/ecommerce/ecommerce_order.php');
			$EcommerceOrder = new ecommerce_order();
			
			$order_detail = $EcommerceOrder->getFullDetail($order_id);
			
			/**
			 * find if the order contains gift
			 */
			
			if ($voucher_basket_items = $this->getVoucherBasketItems($order_detail, $gift_voucher_product_id)) {
			
				return $this->generateVouchers($voucher_basket_items);
				
			}
			
		}
		
		return true;
	}
	
	/**
	 * getGiftVoucherProductId
	 */
	 
	public function getGiftVoucherProductId($order_id) {
		
		if (!is_numeric($order_id)) return false;

		/**
		 * get product conf
		 */
		 
		require_once('models/ecommerce/ecommerce_product.php');
		$ecommerce_product_conf = ecommerce_product::initConfiguration();
		
		/**
		 * check gift voucher product ID is set
		 */
		 
		if (!is_numeric($ecommerce_product_conf['gift_voucher_product_id']) || $ecommerce_product_conf['gift_voucher_product_id']  == 0) {
			
			msg("ecommerce_product.gift_voucher_product_id conf option is not defined", 'error');
			
			return false;
		}
		
		return $ecommerce_product_conf['gift_voucher_product_id'];
	}
	
	/**
	 * getVoucherBasketItems
	 */
	 
	public function getVoucherBasketItems($order_detail, $gift_voucher_product_id) {
		
		if (!is_array($order_detail)) return false;
		if (!is_numeric($gift_voucher_product_id)) return false;
		
		$voucher_basket_items = array();
		
		foreach ($order_detail['basket']['items'] as $basket_item) {
			
			if ($basket_item['product']['id'] == $gift_voucher_product_id) {
				$voucher_basket_items[] = $basket_item;
			}
			
		}
		
		if (count($voucher_basket_items) > 0) return $voucher_basket_items;
		else return false;
		
	}
	
	/**
	 * generateVouchers
	 */
	 
	public function generateVouchers($voucher_basket_items) {
	
		if (!is_array($voucher_basket_items)) return false;
		
		foreach ($voucher_basket_items as $item) {
			
			$this->generateSingleVoucher($item);
			
		}
		
		return true;
	}
	
	/**
	 * generateSingleVoucher
	 */
	
	public function generateSingleVoucher($voucher_basket_item) {
		
		if (!is_array($voucher_basket_item)) return false;
		
		$voucher_data = array();
		$voucher_data['variety_id'] = $voucher_basket_item['product_variety_id'];
		$voucher_data['recipient_name'] = $voucher_basket_item['other_data']['recipient_name'];
		$voucher_data['recipient_email'] = $voucher_basket_item['other_data']['recipient_email'];
		$voucher_data['message'] = $voucher_basket_item['other_data']['message'];
		$voucher_data['sender_name'] = $voucher_basket_item['other_data']['sender_name'];
		
		if (!$this->validateData($voucher_data)) {
		
			msg("Voucher data are not valid", 'error');
			return false;
			
		}
		
		/**
		 * create discount code
		 */
		
		$promotion_data = array();
		$promotion_data['code_pattern'] = "GIFT-{$voucher_basket_item['id']}";
		$promotion_data['title'] = "GIFT-VOUCHER-{$voucher_basket_item['id']}";
		$promotion_data['discount_percentage_value'] = 0;
		$promotion_data['discount_fixed_value'] = $voucher_basket_item['total_inc_vat'];
		$promotion_data['uses_per_coupon'] = $voucher_basket_item['quantity'];
		$promotion_data['other_data'] = $voucher_basket_item['other_data'];
		$promotion_data['publish'] = 1;
		
		require_once('models/ecommerce/ecommerce_promotion.php');
		$Promotion = new ecommerce_promotion();
		
		if ($promotion_id = $Promotion->addPromotion($promotion_data)) {
			msg("Promotion code {$promotion_data['code_pattern']} generated as promotion ID $promotion_id", 'ok', 1);
		} else {
			msg('Promotion code generation failed', 'error');
			//return false;
		}
		
		/**
		 * create the voucher file
		 */
		 
		$url = "http://{$_SERVER['SERVER_NAME']}/request/sys/html5.node/site/print.component/ecommerce/gift_voucher~voucher_code={$promotion_data['code_pattern']}~";
		$filename = ONXSHOP_PROJECT_DIR . "var/tmp/voucher_basket_item_id_{$voucher_basket_item['id']}.png";
		
		$shell_command = "wkhtmltoimage $url $filename";
		
		if ($result = local_exec($shell_command)) {
			msg("File $filename generated by wkhtmltoimage", 'ok', 1);
		}
		
		/**
		 * send email
		 */
		 
		$GLOBALS['common_email_form'] = array('promotion_data'=>$promotion_data, 'voucher_data'=>$voucher_data);
		$GLOBALS['onxshop_atachments'] = array($filename);
		
		require_once('models/common/common_email_form.php');
		$EmailForm = new common_email_form();
		
		$template = 'gift_voucher';
		$content = $filename;
		$email_recipient = $voucher_data['recipient_email'];
		$name_recipient = $voucher_data['recipient_name'];
		$email_from = false;
		$name_from = false;
		
		$EmailForm->sendEmail($template, $content, $email_recipient, $name_recipient, $email_from, $name_from);
		
		unset($GLOBALS['common_email_form']);
		unset($GLOBALS['onxshop_atachments']);
		
		return true;
	}
}
