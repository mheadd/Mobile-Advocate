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
$sql = "SELECT CONCAT(sen_firstName,' ',sen_middleName,' ',sen_lastName,' ',sen_suffix) AS sen_name,sen_district,sen_phone,sen_party,CONCAT(amb_firstName,' ',amb_middleName,' ',amb_lastName,' ',amb_suffix) AS amb_name,amb_district,amb_phone,amb_party FROM citizen 
WHERE phone_number = $phone_number";
$lookup_failed = 0;

try {
	$db = new dbConnect(DB_HOST, DB_USER, DB_PASS);
	$db->selectDB(DB_NAME);
	$result = $db->runQuery($sql);
	
	if ($db->getNumRowsAffected() == 0) {
		$lookup_failed = 1;
	}
	
	$legislatorDetails = mysql_fetch_assoc($result);	
}

catch (connectionException $ex) {
	header("HTTP/1.0 500 Server Error");
	die();
}		
catch (Exception $ex) {
	header("HTTP/1.0 500 Server Error");
	die();	
}

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<vxml version = "2.1">
	<form id="F_1">
		<block>
			<var name="lookupFailed" expr="<?php echo $lookup_failed; ?>"/>
			<var name="senatorName" expr="'<?php echo $legislatorDetails['sen_name']; ?>'"/>
			<var name="senatorParty" expr="'<?php echo $legislatorDetails['sen_party']; ?>'"/>
			<var name="senatorDistrict" expr="'<?php echo $legislatorDetails['sen_district']; ?>'"/>
			<var name="senatorPhone" expr="'<?php echo $legislatorDetails['sen_phone']; ?>'"/>
			<var name="assemblyMemberName" expr="'<?php echo $legislatorDetails['amb_name']; ?>'"/>
			<var name="assemblyMemberParty" expr="'<?php echo $legislatorDetails['amb_party']; ?>'"/>
			<var name="assemblyMemberDistrict" expr="'<?php echo $legislatorDetails['amb_district']; ?>'"/>
			<var name="assemblyMemberPhone" expr="'<?php echo $legislatorDetails['amb_phone']; ?>'"/>
			<return namelist="lookupFailed senatorName senatorParty senatorDistrict senatorPhone assemblyMemberName assemblyMemberParty assemblyMemberDistrict assemblyMemberPhone"/>
		</block>
	</form>
</vxml>