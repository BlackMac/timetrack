<?php

// I don't like functions.php
// We should put whole timetrack into classes.
// As long as it stays procedural, we got to cope with functions.php

function detectMobileDevices() {
	$container = $_SERVER['HTTP_USER_AGENT'];
	// The below prints out the user agent array. Uncomment to see it shown on the page.
	// print_r($container);

	// Add whatever user agents you want here to the array if you want to make this show on a Blackberry
	// or something. No guarantees it'll look pretty, though!
	$useragents = array("iPhone", "iPod", "aspen", "dream", "incognito", "webmate", "BlackBerry9500", "BlackBerry9530");
	$applemobile = false;
	foreach ($useragents as $useragent) {
		if (eregi($useragent, $container)) {
			$applemobile = true;
		}
	}

	return $applemobile;
}

function formatmonth($m) {
	$months = array('Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember');
	$year = substr($m, 0, 4);
	$month = substr($m, 4, 2);
	return sprintf("%s %s", $months[$month-1], $year);
}
