<?php
/*
 * Copyright 2009 Mark J. Headd
 * 
 * This file is part of MobileAdvocate
 * 
 * MobileAdvocate is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * MobileAdvocate is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with MobileAdvocate.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */

/*
 * Class to invoke the IMified API and send an SMS message to a user.
 */

if (!class_exists(apiBaseClass)) { require('class.apibaseclass.php'); }

class sendsms extends apiBaseClass {
	
	// API return info
	private $output;
	private $info;
	private $error;
	
	public function getOutput() {
		return $this->output;
	}
	
	public function getInfo() {
		return $this->info;
	}
	
	public function getError() {
		return $this->error;
	}
	
	public function __construct($baseURL){
		parent::__construct($baseURL);
	}
	
	public function invoke(Array $data, $user, $password) {
		
		curl_setopt($this->ch, CURLOPT_URL, $this->baseURL);
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_USERPWD, $user.':'.$password);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 100);
		
		$this->output = curl_exec($this->ch);
		$this->info = curl_getinfo($this->ch);
		$this->error = curl_error($this->ch);
	}
	
	public function logResults($logFile, $message) {
		parent::logResults($logFile, $message);
	}
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>