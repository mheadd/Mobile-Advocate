<?php

// Constants used to look up representatives for a given address.
define("REP_LOOKUP_BASE_URL", "http://174.129.25.59/api/ny/2009-2010/%type%/districts/geo/");
define("REP_LOOKUP_FORMAT", "xml");

// Constants used to geocode an address.
define("MAPS_LOOKUP_BASE_URL", "http://maps.google.com/maps/geo");
define("MAPS_LOOKUP_FORMAT", "xml");
define("MAPS_API_KEY", "your_api_key_goes_here");

// Database access credentials.
define("DB_HOST", "");
define("DB_NAME", "");
define("DB_USER", "");
define("DB_PASS", "");

// IMified API credentials
define("SEND_SMS_BASE_URL", "https://www.imified.com/api/bot/");
define("SEND_SMS_USER_NAME", "");
define("SEND_SMS_PASSWORD", "");
define("SEND_SMS_BOT_KEY", "your_bot_key_goes_here");

// Message sent to recruiter when service is down.
define("SERVICE_ERROR_MESSAGE", "Sorry. The serice is experiencing problems: ");

// Location to save recording files.
define("RECORDING_FILE_LOCATION", "");

?>
