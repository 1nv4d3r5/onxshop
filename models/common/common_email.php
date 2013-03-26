<?php
/**
 * class common_email
 *
 * Copyright (c) 2009-2011 Laposa Ltd (http://laposa.co.uk)
 * Licensed under the New BSD License. See the file LICENSE.txt for details.
 *
 */
 
class common_email extends Onxshop_Model {

	/**
	 * @access private
	 */
	var $id;
	/**
	 * @access private
	 */
	var $email_from;
	/**
	 * @access private
	 */
	var $name_from;
	/**
	 * @access private
	 */
	var $subject;
	/**
	 * @access private
	 */
	var $content;
	
	var $template;
	
	/**
	 * @access private
	 */
	var $email_recipient;
	/**
	 * @access private
	 */
	var $name_recipient;
	
	var $created;
	
	var $ip;
	
	var $_metaData = array(
		'id'=>array('label' => 'ID', 'validation'=>'int', 'required'=>true), 
		'email_from'=>array('label' => '', 'validation'=>'email', 'required'=>true),
		'name_from'=>array('label' => '', 'validation'=>'string', 'required'=>true),
		'subject'=>array('label' => '', 'validation'=>'string', 'required'=>true),
		'content'=>array('label' => '', 'validation'=>'string', 'required'=>true),
		'template'=>array('label' => '', 'validation'=>'string', 'required'=>true),
		'email_recipient'=>array('label' => '', 'validation'=>'email', 'required'=>true),
		'name_recipient'=>array('label' => '', 'validation'=>'string', 'required'=>true), 
		'created'=>array('label' => '', 'validation'=>'datetime', 'required'=>true),
		'ip'=>array('label' => '', 'validation'=>'string', 'required'=>true)
	);


	var $_form_data_valid;

	/**
	 * create table sql
	 * 
	 * @return string
	 * SQL command for table creating
	 */
		 
	private function getCreateTableSql() {
	
		$sql = "
CREATE TABLE common_email (
    id serial NOT NULL PRIMARY KEY,
    email_from character varying(255),
    name_from character varying(255),
    subject character varying(255),
    content text,
    template character varying(255),
    email_recipient character varying(255),
    name_recipient character varying(255),
    created timestamp(0) without time zone,
    ip character varying(255)
);
		";
		
		return $sql;
	}

	/**
	 * init configuration
	 * 
	 * @return array
	 * configuration
	 */
	 
	static function initConfiguration() {
	
		if (array_key_exists('common_email', $GLOBALS['onxshop_conf'])) $conf = $GLOBALS['onxshop_conf']['common_email'];
		else $conf = array();
		
		/**
		 * set default values if empty
		 */
		// what is default email FROM address?
		if ($conf['mail_sender_address'] == '') $conf['mail_sender_address'] = $GLOBALS['onxshop_conf']['global']['admin_email'];
		if ($conf['mail_sender_name'] == '') $conf['mail_sender_name'] = $GLOBALS['onxshop_conf']['global']['admin_email_name'];
		// what is default email TO address?
		if ($conf['mail_recipient_address'] == '') $conf['mail_recipient_address'] = $GLOBALS['onxshop_conf']['global']['admin_email'];
		if ($conf['mail_recipient_name'] == '') $conf['mail_recipient_name'] = $GLOBALS['onxshop_conf']['global']['admin_email_name'];
		// where to send blind copy of all email generated by the system?
		if ($conf['mail_bcc_address'] == '') $conf['mail_bcc_address'] = false;
		if ($conf['mail_bcc_name'] == '') $conf['mail_bcc_name'] = false;
		// if the COMMON_EMAIL_FORM_SMTP_SERVER is defined, we'll use that SMTP server
		if ($conf['smtp_server_address'] == '') $conf['smtp_server_address'] = false;
		if ($conf['smtp_server_username'] == '') $conf['smtp_server_username'] = false;
		if ($conf['smtp_server_password'] == '') $conf['smtp_server_password'] = false;

		return $conf;
	}
	
	/**
	 * explore form data
	 * 
	 * @param array $items
	 * item list
	 * 
	 * @return string
	 * formated text message
	 */
	
	function exploreFormData($items) {
	
		$this->_form_data_valid = array();

		if (count($items) > 0) {
		
			//check for require items and creating of message
			while (list ($key, $val) = each ($items)) {

				if (preg_match('/required/', $key) && trim($val) == "") {
					$key = $this->_formatKey($key);
					msg("Missing: $key", 'error');
					$this->setFormDataValid($key, false);
				}

				$key = $this->_formatKey($key);

				// the set() is stripping \n
				if ($key == 'Message') {
					$c = "<br />$key:\n$val<br />";
				} else {
					$c = "$key: $val";
				}

				$c = "\n$c";

				$content = $content . $c;
			}

			if ($this->getFormDataValid()) $this->setValid('content', true);
			else $this->setValid('content', false);

			return $content;
		}
	}
	
	/**
	 * get valid
	 * 
	 * @return boolean
	 * form validation state
	 */
  
	function getFormDataValid() {
	
		if (is_array($this->_form_data_valid)) {
			if (in_array(false, $this->_form_data_valid)) return false;
			else return true;
		} else return false;
	}

	/**
	 * set valid
	 * 
	 * @param string $key
	 * item key name
	 * 
	 * @param string $value
	 * item value
	 */
	 
	function setFormDataValid($key, $value) {
	
		$this->_form_data_valid[$key] = $value;
		//print_r($this->_valid);
	}

	/**
	 * format key
	 * 
	 * @param string $key
	 * item key name
	 */
	 
	function _formatKey($key) {
	
		$key = preg_replace("/required_/","",$key);
		$key = preg_replace("/_/"," ",$key);
		$key = ucfirst($key);
		return $key;
	}

	/**
	 * send e-mail
	 * 
	 * @param string $template
	 * template file name
	 * 
	 * @param string $content
	 * message content
	 * 
	 * @param string $email_recipient
	 * recipient e-mail address
	 * 
	 * @param string $name_recipient
	 * recipient display name
	 * 
	 * @param string $email_from
	 * sender e-mail address
	 * 
	 * @param string $name_from
	 * sender display name
	 * 
	 * @return boolean
	 * send status
	 */
	function sendEmail($template, $content = 'n/a', $email_recipient = false, $name_recipient = false, $email_from = false, $name_from = false) {
		
		if (!$email_recipient) $email_recipient = $this->conf['mail_recipient_address'];
		if (!$name_recipient) $name_recipient = $this->conf['mail_recipient_name'];
		if (!$email_from) $email_from = $this->conf['mail_sender_address'];
		if (!$name_from) $name_from = $this->conf['mail_sender_name'];
		
		$email_data = array();
		$email_data['content'] = $content;
		$email_data['email_from'] = $email_from;
		$email_data['name_from'] = $name_from;
		$email_data['template'] = $template;
		$email_data['email_recipient'] = $email_recipient;
		$email_data['name_recipient'] = $name_recipient;
		$email_data['created'] = date('c');
		$email_data['ip'] = $_SERVER['REMOTE_ADDR'];
		
		//temp
		$this->set('content', $email_data['content']);
		$this->set('email_from', $email_data['email_from']);
		$this->set('name_from', $email_data['name_from']);
		$this->set('template', $email_data['template']);
		$this->set('email_recipient', $email_data['email_recipient']);
		$this->set('name_recipient', $email_data['name_recipient']);
		$this->set('created', $email_data['created']);
		$this->set('ip', $email_data['ip']);
 	
 		//$this->setAll($email_data);
 		
		if ($this->getValid()) {
		
			if ($this->send()) {
				//temp
				$email_data['subject'] = $this->get('subject');
				
				if ($this->insert($email_data)) {
					return true;
				} else {
					//TODO: notify admin
					msg("Can't insert email record into the database", "error", 1);
				}
		
			} else {
				//TODO: notify admin
				msg("Can't send email using mail->send()", "error");
			}
			
		} else {
	        	return false;
		}
  	}
	
	/**
	 * send
	 * internal function
	 * 
	 * @return boolean
	 * send status
	 */
	 
	function send() {
	
		$email_data = $this->_format($this->template);
		$this->set('subject', $email_data['title']);

		require_once 'Zend/Mail.php';
		
		$mail = new Zend_Mail('utf-8');
		$mail->addHeader('X-MailGenerator', ONXSHOP_VERSION);

		$mail->setFrom($this->get('email_from'), $this->get('name_from'));
		$mail->addTo($this->get('email_recipient'), $this->get('name_recipient'));
		$mail->setSubject($this->get('subject'));


		//send BCC of all emails to specified address
		if ($this->conf['mail_bcc_address']) $mail->addBcc($this->conf['mail_bcc_address'], $this->conf['mail_bcc_name']);
 
		if ($this->conf['smtp_server_address']) {
			msg ("use SMTP " . $this->conf['smtp_server_address'], 'ok', 2);

			require_once 'Zend/Mail/Transport/Smtp.php';
			
			if ($this->conf['smtp_server_username'] && $this->conf['smtp_server_password']) {
				msg('using SMTP auth', 'ok', 2);
				$config = array('auth' => 'login',
                			'username' => $this->conf['smtp_server_username'],
                			'password' => $this->conf['smtp_server_password']);
			} else {
				$config = array();
				msg("Not using SMTP auth", 'ok', 2);
			}

			$transport = new Zend_Mail_Transport_Smtp($this->conf['smtp_server_address'], $config);
			Zend_Mail::setDefaultTransport($transport);
		} else {
			msg('use internal mail()', 'ok', 2);
		}

		/**
		 * attachment(s) via upload
		 */
		 
		if (count($_FILES) > 0) {
		
			foreach ($_FILES as $key=>$file) {
			
				if (is_uploaded_file($file['tmp_name'])) {
		
					/**
					 * file
					 */
					 
					require_once('models/common/common_file.php');
					//getSingleUpload could be static method
					$CommonFile = new common_file();
					$upload = $CommonFile->getSingleUpload($file, 'var/tmp/');
					
					/**
					 * array indicated the same file name already exists in the var/tmp/ folder
					 * we can ignore it, as the previous attachement was overwritten
					 * FIXME: could be a problem when more users submit the same filename in the same time
					 * perhaps saving file with PHP session id or not saving in var/tmp would help
					 */
					 
					if (is_array($upload)) {
						$attachment_saved_file = ONXSHOP_PROJECT_DIR . $upload['temp_file'];
					} else {
						$attachment_saved_file = ONXSHOP_PROJECT_DIR . $upload;
					}
					
					/**
					 * check if file exists and than add to email as attachemnt
					 */
					 
					if (file_exists($attachment_saved_file)) {
						$attachment_info = $CommonFile->getFileInfo($attachment_saved_file);
						$Attachment = $mail->createAttachment(file_get_contents($attachment_saved_file));
						$Attachment->filename = $attachment_info['filename'];
					}
				}
			}
		}
		
		/**
		 * attachments
		 * quick hack to add attachment functionality, curently used only in gift_voucher_generate
		 */
		 
		if (is_array($GLOBALS['onxshop_atachments']) && count($GLOBALS['onxshop_atachments']) > 0) {
			
			foreach ($GLOBALS['onxshop_atachments'] as $file) {
				
				if (file_exists($file)) {
					
					require_once('models/common/common_file.php');
					$CommonFile = new common_file();
			
					$attachment_info = $CommonFile->getFileInfo($file);
					$Attachment = $mail->createAttachment(file_get_contents($file));
					$Attachment->filename = $attachment_info['filename'];
				}				
				
			}
		
		}

		/**
		 * content alternative text
		 */
		 
		$mail->setBodyText($email_data['content']['txt']);
		$mail->setBodyHtml($email_data['content']['html']);

		/**
		 * send
		 */
		 
		if(!$mail->send()) {
			msg('The email was not sent! Some problem with email sending.', 'error');
			return false;
		} else {
			msg("The email to {$this->email_recipient} has been sent successfully.", 'ok', 2);
			return true;
		}
	}

	/**
	 * format a message
	 * 
	 * @param string $template
	 * message template file name
	 * 
	 * @return array
	 * formated message data
	 */
	function _format($template) {
	
		$orig = $this->get('content');

		$this->content = nl2br($this->get('content'));
		$tpl = new XTemplate("$template.html", getTemplateDir("$template.html", 'mail/')); 		

		$vars = get_object_vars($this);
		$tpl->assign('EMAIL', $vars);

		//read passed data
		if (is_array($GLOBALS['common_email'])) {
			$tpl->assign('DATA', $GLOBALS['common_email']);
		}

		// from $Onxshop_Request->_initTemplateVariables
		if ($_SERVER['HTTPS']) $protocol = 'https';
		else $protocol = 'http';
		
		$tpl->assign('URI', "$protocol://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}");
		$tpl->assign('_SERVER', $_SERVER);
		$tpl->assign('CONFIGURATION', $GLOBALS['onxshop_conf']);
		$tpl->assign('_POST', $_POST);
		$tpl->assign('_GET', $_GET);
		$tpl->assign('TIME', time());
		
		/**
		 * include node configuration
		 */
				
		require_once('models/common/common_node.php');
		$node_conf = common_node::initConfiguration();
		$tpl->assign('NODE_CONF', $node_conf);
		
		/**
		 * parse
		 */

		$tpl->parse('title');
		$tpl->parse('content');

		$this->content = $orig;

		$data['title'] = $tpl->text('title');
		$data['content']['html'] = $tpl->text('content');

		//get the text version
		$data['content']['txt'] = html2text($data['content']['html']);

		//convert relative links to absolute
		$data['content']['html'] = $this->rel2abs("http://{$_SERVER['HTTP_HOST']}", $data['content']['html']);
		$data['content']['txt'] = $this->rel2abs("http://{$_SERVER['HTTP_HOST']}", $data['content']['txt']);

		return $data;
	}

	
	/**
	 * rewrite relative link to absolute, ie when integration a remote source
	 *
	 * @param string $url
	 * absolute prefix of url
	 * 
	 * @param string $html
	 * text for changing
	 * 
	 * @return string
	 * changed text
	 */
	 
	function rel2abs($url, $html) {
	
		/*
		href="" ''
		no javascript
		src="" ''
		and href=\'index.php\'
		*/
	    $html = preg_replace("/href=[\"\'](?!JavaScript)(?!http)(.*)[\"\']/i","href=\"$url\\1\"",$html);
	    $html = preg_replace("/src=[\"\'](?!JavaScript)(?!http)(.*)[\"\']/i","src=\"$url\\1\"",$html);
	    $html = preg_replace("/action=[\"\'](?!JavaScript)(?!http)(.*)[\"\']/i","action=\"$url\\1\"",$html);
		//$abs_html = preg_replace( "/(?<!href=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/i", "href=\"$url\\0\"", $html );
	    return $html;
	}
}
