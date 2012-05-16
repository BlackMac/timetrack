<!DOCTYPE html>

<html lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>TimeTrack Jahreskalender</title>
	<meta name="author" content="">
	<link href="static/css/calendar.css" media="screen" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="static/img/favicon.ico" />
	<link rel="icon" href="static/img/favicon.ico" type="image/ico" />
</head>
<body>
<?php
// Comment
session_start();
include "TimeTrack.class.php";
$timetrack = new TimeTrack();

$loggedin=false;
$loggedin = $timetrack->login($_POST['u'], $_POST['p'], $_GET['h']);
$hash = $timetrack->hash;

if(!$loggedin) {
	unset($hash);
	unset($_SESSION['userhash']);
}

if (!$loggedin) {
	die('Not logged in');
}

$year = date("Y");
$holidays = $timetrack->getHolidays($year);
$subjects = $timetrack->getDaySubjectsForYear($year);

for ($month = 1; $month <= 12; $month++)
{
	$monthtimestamp = mktime(0, 0, 0, $month, 1, $year);
	$monthname = strftime("%B %Y", $monthtimestamp);

	$firstweekday = date('w', $monthtimestamp);
	if($firstweekday == 0)
		$firstweekday = 7; // sunday should be last day
	$firstweekday--; // let begin with 0

	$week = 1;

	?>
<table class="month" cellspacing=0 cellpadding=0>
	<thead>
		<tr>
			<th colspan="7" align="center"><?php echo $monthname; ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="weekdays">
			<td>Mo</td>
			<td>Di</td>
			<td>Mi</td>
			<td>Do</td>
			<td>Fr</td>
			<td class="nobusiness">Sa</td>
			<td class="nobusiness">So</td>
		</tr>
		<tr>
	<?php
	echo str_repeat('<td>&nbsp;</td>', $firstweekday);
	for ($day = 1; $day <= date('t', $monthtimestamp); $day++)
	{
		$todaytimestamp = mktime(0, 0, 0, $month, $day, $year);

		$class = "";
		if(isset($subjects[date('Y-m-d', $todaytimestamp)]))
		{
			$class = " " . $subjects[date('Y-m-d', $todaytimestamp)]['subject'];
		}

		$weekday = date('w', $todaytimestamp);
		if(isset($holidays[$todaytimestamp]))
		{
			echo "<td class='holiday $class' title='".$holidays[$todaytimestamp]."'>$day</td>";
		}
		elseif($weekday == 0 || $weekday == 6)
		{
			echo "<td class='nobusiness $class'>$day</td>";
		}
		else
		{
			echo "<td class='$class'>$day</td>";
		}

		if($weekday == 0) // sunday should be last day
		{
			echo "</tr><tr>";
			$week++;
		}
	}

	// fill last week for empty days
	$lastweekday = date('w', mktime(0, 0, 0, $month, $day, $year));
	if($lastweekday == 0)
		$lastweekday = 7; // sunday should be last day
	$lastweekday--; // let begin with 0
	echo str_repeat('<td>&nbsp;</td>', 7 - $lastweekday);

	// fill every month to six weeks
	if($week < 6)
	{
		for ($missingweek = $week; $missingweek < 6; $missingweek++)
		{
			echo "</tr><tr>";
			echo str_repeat('<td>&nbsp;</td>', 7);
		}
	}
	?>
			</tr>
	</tbody>
</table>
<?php
}
?>
<br style="clear: both;">
<table cellspacing=0 style="width: 100%; text-align: center;">
	<tr>
		<th colspan="4">Legende</th>
	</tr>
	<tr>
		<td class="illness">Krank</td>
		<td class="vacation">Urlaub</td>
		<td class="holiday">Feiertag</td>
		<td class="nobusiness">Betriebsfrei</td>
	</tr>
</table>

</body>
</html>