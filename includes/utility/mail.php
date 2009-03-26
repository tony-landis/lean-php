<?php

require_once "includes/class/phpmailer/class.phpmailer.php";

class email_utility extends PHPMailer
{
	function __construct() {
		$this->IsHTML(false); 
		$this->setFrom(ssetup::get('mail_from'), ssetup::get('mail_name'));	
		if(ssetup::get('mail_smtp'))
		{
			$this->IsSMTP();  
			$this->SMTPAuth = true;                   
			$this->Host     = ssetup::get('mail_smtp_host');
			$this->Username = ssetup::get('mail_smtp_user');
			$this->Password = ssetup::get('mail_smtp_pass');
		}
	}
	
	/**
	 * Set the recipient address and name
	 *
	 * @param string $email
	 * @param string $name
	 */
	public function setTo($email, $name) {
		$this->AddAddress($email,$name); 
	}
	
	/**
	 * Set the subject
	 *
	 * @param string $subject
	 */
	public function setSubject($subject) {
		$this->Subject=$subject;
	}
	
	/**
	 * Set the body content
	 *
	 * @param string $body
	 */
	public function setBody($body) {
		$this->Body=$body;
	}
	
	/**
	 * Set the sender info
	 *
	 * @param string $email
	 * @param string $name
	 */
	public function setFrom($email, $name) {
		$this->From = $email;
		$this->FromName = $name;
	}	
}
?>