#!/usr/bin/php
<?php
include "../TimeTrack.class.php";
$dir = dirname(dirname(__FILE__));
$files = glob($dir . '/logs/*/options.ini');
$core = new TimeTrack();
$core->setMonth(date("Ym"));

foreach ($files as $optionFileName)
{
	$options = json_decode(file_get_contents($optionFileName));
	$hash = basename(dirname($optionFileName));
	if(isset($options->notifications))
	{
		writeLog("'$hash': Found notification");

		if(! isset($options->notifications->when) || ! isset($options->notifications->what) || ! isset($options->notifications->how))
		{
			writeLog("'$hash': Notification settings are incomplete. Skipping.");
			continue;
		}

		if(! isset($options->notifications->enabled) || $options->notifications->enabled == false)
		{
			writeLog("'$hash': Notification is disabled. Skipping.");
			continue;
		}

		$core->login(null, null, $hash);
		$core->parseData();
		$lastdayData = $core->getLastDay();
		if(date('Y-m-d') != $lastdayData['date'])
		{
			writeLog('Last day is not today, skipping');
			continue;
		}
		$compareDate = $core->getNormalDayEnd();
		if($options->notifications->what == "earliest")
		{
			$compareDate = $core->getEarliestDayEnd();
		}
		$neededMaxGap = $options->notifications->when * 60;
		$actualGap = $compareDate - time();
		if($actualGap <= 60 || $actualGap < 0)
		{
			continue;
		}

		sendNotification($options->notifications, $compareDate, $lastdayData);
	}

}

function sendNotification($notification, $end, $lastday)
{
	$text = getMessageText($notification->how, $notification->what, $notification->when, $end);
	switch ($notification->how)
	{
		case 'mail':
			mail($notification->target, "Bald ist Ende", $text);
			writeLog("Mail sent to '" . $notification->target . "'");
			break;
		case 'sms':
			;
			break;
		case 'iphone':
			;
			break;
		default:
			writeLog("Found unsupported notification type '" . $notification->how . "'. Exiting...");
			return;
	}
}

function getMessageText($how, $what, $when, $end)
{
	$type = "normaler";
	if($when == "earliest")
	{
		$type = "frühestmöglicher";
	}
	if($how == "mail")
	{
		return sprintf("In %s Minuten ist dein %s Feierabend. Packe um %s deine Sachen und geh.", $when, $type, date("G:i:s", $end));
	}
	elseif($how == "sms")
	{
		return sprintf("In %s Minuten ist dein %s Feierabend. Packe um %s deine Sachen und geh.", $when, $type, date("G:i:s", $end));
	}
	elseif($how == "iphone")
	{
		return sprintf("In %s Minuten ist dein %s Feierabend. Packe um %s deine Sachen und geh.", $when, $type, date("G:i:s", $end));
	}
	return "";
}

function writeLog($text)
{
	echo "[" . date("c") . "] " . wordwrap($text, 75, "\n\t") . "\n";
}