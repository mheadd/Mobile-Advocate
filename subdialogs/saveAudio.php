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

$segmentName = $_REQUEST['callerid'];
$show = $_FILES['callSummary']['tmp_name'];

$fileName = RECORDING_FILE_LOCATION.$segmentName.'.wav';

if(@move_uploaded_file($show, $fileName)) {
	echo '<?xml version="1.0" encoding="utf-8"?>';
}
else {	
	echo 'ERROR';
}
?>
<status>
<value>ok</value>
</status>