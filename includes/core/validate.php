<?php

/** 
 * Simple validation class
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Utility
*/
class Validate
{
	var $value;
	var $status;
	var $error;
	
	/* call into validation */
	function Validate($type, $value=false)
	{
		$this->value=$value;
		if(ereg('^regex:',$type)) {
			$this->doRegex(ereg_replace('^regex:','',$type));
			return;	
		} 
		switch ($type) 
		{  
			/* email */
			case 'email':
				$this->doRegex("^[a-zA-Z0-9\._-]+@+[a-zA-Z0-9\._-]+\.+[a-zA-Z]{2,4}$");
				$this->setError("The e-mail address provided is not valid");
			break;	
			
			case 'domain':
				$this->doRegex("^([a-z0-9]([-a-z0-9]*[a-z0-9])?.)+((a[cdefgilmnoqrstuwxz]|aero|arpa)|(b[abdefghijmnorstvwyz]|biz)|(c[acdfghiklmnorsuvxyz]|cat|com|coop)|d[ejkmoz]|(e[ceghrstu]|edu)|f[ijkmor]|(g[abdefghilmnpqrstuwy]|gov)|h[kmnrtu]|(i[delmnoqrst]|info|int)|(j[emop]|jobs)|k[eghimnprwyz]|l[abcikrstuvy]|(m[acdghklmnopqrstuvwxyz]|mil|mobi|museum)|(n[acefgilopruz]|name|net)|(om|org)|(p[aefghklmnrstwy]|pro)|qa|r[eouw]|s[abcdeghijklmnortvyz]|(t[cdfghjklmnoprtvwz]|travel)|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])$");
				$this->setError("The domain name provided is not valid");
			break;	
							
			/* ip address, matches 0.0.0.0 through 255.255.255.255 */
			case 'ip':
				$this->doRegex('^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$');
				$this->setError("The IP address provided is not valid");
			break;
			
			case 'numeric':
				$this->doRegex('[0-9]');
				$this->setError("The value provided in not numeric");
			break;
			
			case 'alpanumeric':
				$this->doRegex('[0-9a-zA-Z]');
				$this->setError("The value provided in not alpahnumeric");
			break; 
						
			/* no match for type */	
			default: 
				$this->setStatus(false);
				$this->setError("Invalid validation type specified");
			break;
		}
	}
	
	/* handle regex */
	public function doRegex($r) { 
		$this->setStatus(ereg($r, $this->value));
	}				
	public function setStatus($s) {
		$this->status=$s;
	}
	public function getStatus() {
		return $this->status;
	}
	public function setError($e) {
		$this->error=$e;
	}
	public function getError() {
		return $this->error;
	}
}
?>