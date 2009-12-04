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

require('../config.php');

// Function to load class files as needed.
function __autoload($class_name) {
    require_once ('../classes/class.'.strtolower($class_name).'.php');
}

$phone_number = $_REQUEST['callerid'];
$call_length = $_REQUEST['callLength'];
$recording_made = (trim($_REQUEST['recordingMade']) == 'true' ? 1 : 0);
$allow_map_option = (trim($_REQUEST['allowMapOption']) == 'true' ? 1 : 0);
$call_log_message = "Call record logged successfully";

$sql = "INSERT INTO calls (citizen_phone, call_datetime, call_length, recorded_message, map_call) VALUES ($phone_number, NOW(), $call_length, $recording_made, $allow_map_option)";

try {
	$db = new dbConnect(DB_HOST, DB_USER, DB_PASS);
	$db->selectDB(DB_NAME);
	$result = $db->runQuery($sql);	
}

catch (connectionException $ex) {
	$call_log_message = "An error occured when logging the call for $phone_number: ".$ex->getMessage();
}		
catch (Exception $ex) {
	$call_log_message = "An error occured when logging the call for $phone_number: ".$ex->getMessage();	
}
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<vxml version = "2.1">
	<form id="F_1">
		<block>
			<log>*** <?php echo $call_log_message; ?> ***</log>
			<exit/>
		</block>
	</form>
</vxml>