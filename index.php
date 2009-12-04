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

require('config.php');

// Function to load class files as needed.
function __autoload($class_name) {
    require_once ('classes/class.'.strtolower($class_name).'.php');
}

// Function to strip non-digit characters from phone number.
function getDigits($number) {
	$filterChars = Array("(", ")", " ", "-");
	return str_replace($filterChars, "", $number);
}

// Function to validate a phone number.
function validatePhoneNumber($number) {
	if (strlen($number) != 10) {
		return false;
	}
	return true;
}

// Function to parse XML data returned from Fifty State API and extract legislator details.
function getLegislatorDetails($xmlDoc) {
	
	$xml = new SimpleXmlElement($xmlDoc);	
	$legislatorDetails = Array();
	$legislatorDetails['firstName'] = $xml->legislators->resource->first_name;
	$legislatorDetails['middleName'] = $xml->legislators->resource->middle_name;
	$legislatorDetails['lastName'] = $xml->legislators->resource->last_name;
	$legislatorDetails['suffix'] = $xml->legislators->resource->suffix;
	$legislatorDetails['district'] = $xml->legislators->resource->roles->resource->district;
	$legislatorDetails['phone'] = getDigits($xml->legislators->resource->roles->resource->contact_info->resource->phone);
	
	// If party value is comma delimited list, get only first listed.
	$party = $xml->legislators->resource->roles->resource->party;
	if (strpos($party, ",")) {
		$legislatorDetails['party'] = substr($party, 0, strpos($party, ","));
	}
	else {
		$legislatorDetails['party'] = $party;
	}
	
	return $legislatorDetails;
}

// Return an error message that the serice is experiencing issues.
function getServiceErrorMessage($exception) {	
	return SERVICE_ERROR_MESSAGE.$exception->getMessage()."<reset>";	
}

// Instantiate new IM bot object.
$advocateBot = new imified($_POST);

// Instantiate new DB conenction object.
$db = new dbConnect(DB_HOST, DB_USER, DB_PASS);

/*
 * Phone number verification (Bot step 1).
 */
if ($advocateBot->getStep() == 1) {
	
	// Check to see if recruiter requested reset.
	if (substr(trim($advocateBot->getMsg()), 0, 6) == '#reset') {
		die("Enter a 10-digit voter telephone number.<reset>");
	}
	
	// Check to see if recruiter requested to clear a number from the database (only if record created by same recruiter).
	if (substr(trim($advocateBot->getMsg()), 0, 6) == '#clear') {
		try {
			$phone_number = getDigits(trim(substr($advocateBot->getMsg(), 6, 15)));
			$recruiter_id = $db->escapeInput($advocateBot->getUserkey());
			$db->selectDB(DB_NAME);
			$sql = "DELETE FROM citizen WHERE phone_number = '$phone_number' AND recruiter_id = '$recruiter_id'";
			$db->runQuery($sql);	
			die("Enter a 10-digit voter telephone number.<reset>");
		}
		catch (connectionException $ex) {
			die(getServiceErrorMessage($ex));
		}		
		catch (Exception $ex) {
			die(getServiceErrorMessage($ex));
		}
	}
	
	if (validatePhoneNumber(getDigits($advocateBot->getMsg()))) {
		
		try {
			$phone_number = getDigits($advocateBot->getMsg());
			$recruiter_id = $db->escapeInput($advocateBot->getUserkey());
			$db->selectDB(DB_NAME);
			$sql = "INSERT INTO citizen (phone_number, recruiter_id, step) VALUES ('$phone_number', '$recruiter_id', 1)";
			$db->runQuery($sql);
			die("Enter the full name of the voter.");
		}
		catch (connectionException $ex) {
			die(getServiceErrorMessage($ex));
		}		
		catch (Exception $ex) {
			die(getServiceErrorMessage($ex));
		}	
	}
	else {
		die("Please enter a valid 10-digit voter telephone number.<reset>");
	}
}

/*
 * Get voter name (Bot step 2).
 */

if ($advocateBot->getStep() == 2) {
	try {
			$phone_number = getDigits($advocateBot->getValue(0));	
			$name = $db->escapeInput($advocateBot->getMsg());
			$db->selectDB(DB_NAME);
			$sql = "UPDATE citizen SET `name` = '$name' WHERE phone_number = $phone_number";
			$db->runQuery($sql);
			die("Enter the full address for $name.");
		}
		catch (connectionException $ex) {
			die(getServiceErrorMessage($ex));
		}		
		catch (Exception $ex) {
			die(getServiceErrorMessage($ex));
		}		
}

/*
 * Location lookup (Bot step 3).
 */
if ($advocateBot->getStep() == 3) { 
	
	// Check to see if recruiter requested reset.
	if (trim($advocateBot->getMsg()) == '#reset') {
		try {
			$phone_number = getDigits($advocateBot->getValue(0));
			$db->selectDB(DB_NAME);
			$sql = "DELETE FROM citizen WHERE phone_number = '$phone_number'";
			$db->runQuery($sql);	
			die("Enter a 10-digit voter telephone number.<reset>");
		}
		catch (connectionException $ex) {
			die(getServiceErrorMessage($ex));
		}		
		catch (Exception $ex) {
			die(getServiceErrorMessage($ex));
		}
	}
		
	$addressString = $advocateBot->getMsg();
	$addressString = str_replace(" ", "+", $addressString);
	
	$geo = new geocode(MAPS_LOOKUP_BASE_URL);
	$geo->invoke($addressString, MAPS_LOOKUP_FORMAT, MAPS_API_KEY, false);
	
	$geoInfo = $geo->getInfo();
	if ($geoInfo['http_code'] != '200') {
		die("Unable to geocode address. Try another, or send #reset to start over. <error>");
	}
	
	$geoXML = new SimpleXmlElement($geo->getOutput());
	$address = $geoXML->Response->Placemark->address;
	$coordinates = $geoXML->Response->Placemark->Point->coordinates;
	$coordinatesArray = explode(",", $coordinates);
	$params = Array('lat' => $coordinatesArray[0], 'long' => $coordinatesArray[1], 'format' => REP_LOOKUP_FORMAT);
	try {
			$phone_number = getDigits($advocateBot->getValue(0));
			$sql = "UPDATE citizen SET lat=$coordinatesArray[0],`long`=$coordinatesArray[1],address='$address',step = step+1 WHERE phone_number = '$phone_number'";
			$db->selectDB(DB_NAME);
			$db->runQuery($sql);			
	}
	catch (connectionException $ex) {
		die(getServiceErrorMessage($ex));
	}		
	catch (Exception $ex) {
		die(getServiceErrorMessage($ex));
	}
	
/*
 * Legislator lookup (Bot step 3).
 */
	
	// Look up the Senator for the given address.
	$lookup = new repLookUp(REP_LOOKUP_BASE_URL);
	$lookup->setType("upper");
	$lookup->invoke($params);
	$lookupInfo = $lookup->getInfo();
	if ($lookupInfo['http_code'] != '200') {
		die("Unable to look up Senator. Try another address or send #reset to start over. <goto=1>");
	}
	
	$senateDetails = getLegislatorDetails($lookup->getOutput());
	$senateDetailsSql = "";
	foreach ($senateDetails as $key => $value) {
		$senateDetailsSql .= 'sen_'.$key.'=\''.$value.'\',';
	}
	$senateDetailsSql .= 'step = step+1';
	try {
			$phone_number = getDigits($advocateBot->getValue(0));
			$sql = "UPDATE citizen SET %values% WHERE phone_number = '$phone_number'";
			$sql = str_replace('%values%', $senateDetailsSql, $sql);
			$db->selectDB(DB_NAME);
			$db->runQuery($sql);			
	}
	catch (connectionException $ex) {
		die(getServiceErrorMessage($ex));
	}		
	catch (Exception $ex) {
		die(getServiceErrorMessage($ex));
	}
	
	// Lookup the Assembly member for the given address.
	$lookup->setType("lower");
	$lookup->invoke($params);
	$lookupInfo = $lookup->getInfo();
	if ($lookupInfo['http_code'] != '200') {
		die("Unable to look up Assembly member. Try another address or send #reset to start over. <goto=1>");
	}
	
	$assemblyDetails = getLegislatorDetails($lookup->getOutput());
	$assemblyDetailsSql = "";
	foreach ($assemblyDetails as $key => $value) {
		$assemblyDetailsSql .= 'amb_'.$key.'=\''.$value.'\',';
	}
	$assemblyDetailsSql .= 'step = step+1';
	try {
			$phone_number = getDigits($advocateBot->getValue(0));
			$sql = "UPDATE citizen SET %values% WHERE phone_number = '$phone_number'";
			$sql = str_replace('%values%', $assemblyDetailsSql, $sql);
			$db->selectDB(DB_NAME);
			$db->runQuery($sql);			
	}
	catch (connectionException $ex) {
		die(getServiceErrorMessage($ex));
	}		
	catch (Exception $ex) {
		die(getServiceErrorMessage($ex));
	}
	
	die("Respond #go to send text message to voter.");

}

/*
 * Send details to voter (Bot step 4).
 */

if ($advocateBot->getStep() == 4) {
	
	try {
			$phone_number = getDigits($advocateBot->getValue(0));
			$db->selectDB(DB_NAME);
			$sql = "SELECT * FROM citizen WHERE phone_number = '$phone_number'";
			$result = $db->runQuery($sql);
			
			if ($db->getNumRowsAffected() == 0) {
				throw new connectionException("Could not find record for phone number: $phone_number");
			}
			
			$legislatorDetails = mysql_fetch_assoc($result);		
			
		}
		catch (connectionException $ex) {
			die(getServiceErrorMessage($ex));
		}		
		catch (Exception $ex) {
			die(getServiceErrorMessage($ex));
		}
	
	// Send SMS message with legislator details and number to call	
	$sendToNumber = getDigits($advocateBot->getValue(0));
	$message = 'Senate: '.$legislatorDetails['sen_firstName'].' '.$legislatorDetails['sen_middleName'].' '.$legislatorDetails['sen_lastName'].' '.$legislatorDetails['sen_suffix'].' ('.$legislatorDetails['sen_district'].'-'.$legislatorDetails['sen_party'].'), ';
	$message .= 'Assembly: '.$legislatorDetails['amb_firstName'].' '.$legislatorDetails['amb_middleName'].' '.$legislatorDetails['amb_lastName'].' '.$legislatorDetails['amb_suffix'].' ('.$legislatorDetails['amb_district'].'-'.$legislatorDetails['amb_party'].'). ';
	$message .= 'Dial (646) 434-8986 to contact them.';
	$SMSdata = array('botkey' => SEND_SMS_BOT_KEY, 'apimethod' => 'send', 'user' => $sendToNumber, 'network' => 'sms', 'msg' => $message);
	
	$sendSMSMessage = new sendsms(SEND_SMS_BASE_URL);
	$sendSMSMessage->invoke($SMSdata, SEND_SMS_USER_NAME, SEND_SMS_PASSWORD);
	
	$smsInfo = $sendSMSMessage->getInfo();
	$smsResponse = new SimpleXmlElement($sendSMSMessage->getOutput());
	$smsStatus = $smsResponse->xpath('//rsp/@stat');
	
	if ($smsInfo['http_code'] == '200') {
		if ($smsStatus[0] == 'ok') {
			echo("Success! Text with legislator details and number to call sent to voter at ".$advocateBot->getValue(0)."<reset>");	
			// TODO: Need to update DB when SMS is sent - increment step value.
		}
		else {
			echo("There was a problem sending the message to ".$advocateBot->getValue(0)."<reset>");
		}
	}
	else {
		echo("There was a problem sending the message to ".$advocateBot->getValue(0)."<reset>");
	}

}

?>