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

if (!class_exists(connectionException)) { require('class.connectionexception.php'); }

class dbConnect {
	
	//DB Host
	private $host;
	
	//DB user name
	private $user;
	
	//DB password
	private $password;
	
	//Connection resource
	private $dbConnection;	
	
	//Name of the database to be used
	private $dbName;
	
	//Number of rows affected by last operation
	private $rowsAffected;	
	
	//Debug flag for verbose error output
	private $debug;			
	
	//Error message text
	private $errorMessage;	

	public function __construct($host, $user, $password, $debug=false) {
		
		$this->debug = $debug;
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		
		if(!$this->dbConnection = mysql_connect($this->host, $this->user, $this->password)) {
			
			$this->errorMessage = 'Could not establish connection. ';
			if($this->debug) {
				$this->errorMessage .= mysql_error();
			}
				
			throw new connectionException($this->errorMessage);
				
		}
	}
	
	// Select a database to use
	public function selectDB($name) {
					
		if(!mysql_select_db($name, $this->dbConnection)) {
			
			$this->errorMessage = 'Could not connect to database. ';
			if($this->debug) {
				$this->errorMessage .= mysql_error();
			}
			
			throw new connectionException($this->errorMessage);
			
		}
	}
	
	// Escape input prior to using it in a SQL query
	public function escapeInput($value) {
		
		return mysql_real_escape_string($value, $this->dbConnection);
		
	}
	
	// Run a query against a database table and return the result set
	public function runQuery($query) {
			
			$result = mysql_query($query, $this->dbConnection);
			$this->rowsAffected = mysql_affected_rows();
			
			if(!$result) {
				
			$this->errorMessage = 'Could not execute query. ';
			if($this->debug) {
				$this->errorMessage .= mysql_error();
			}
				throw new connectionException($this->errorMessage);
				
			}
		
			return $result;		
	}
	
	// Determine the number of rows affected in the last operation
	public function getNumRowsAffected() {
		
		return $this->rowsAffected;
	}
	
	// Close the database connection
	public function __destruct() {
		
		mysql_close($this->dbConnection);
		
	}
}

?>