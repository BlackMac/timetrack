<?php
function formatmonth($m) {
	$d=mktime(12,0,0,substr($m, 4, 2),1,substr($m, 0, 4));
	$strdate=gmdate("M Y",$d);
	return $strdate;
}
	
	session_start();
	$loggedin=false;
	$fpath="";
	$hash="";
	
	if (isset($_POST['u']) && isset($_POST['p'])) {
		$hash=md5($_POST['u']."uphashseed".$_POST['p']);
	} elseif (isset($_SESSION['userhash'])) {
		$hash=$_SESSION['userhash'];
	}
	
	$fpath=realpath('logs/'.$hash.'.log');
	if (file_exists($fpath)) $loggedin=true;
		
	if (!$loggedin) {
		if ($hash!="") {
			unset($_SESSION['userhash']);
			header("Location: download.php?h=".$hash);
			exit;
		} else {
			unset($_SESSION['userhash']);
			header("Location: index.php?e=1");
			exit;
		}
	}
	
	$_SESSION['userhash']=$hash;
	
	$days=array();
	$months=array();
	$pausestart=0;
	$f=fopen($fpath, 'r');
	while ($line=fgets($f)) {
		if ($line=="\n") continue;
		if (substr($line,0,1)=="#") continue;
		$coming=(substr($line,0,1)=="+");
		
		$datetime=strtotime(substr($line,2,19))+60*60;
		$monthy=date("Ym",$datetime);
		
		if (!isset($months[$monthy])) {
			$months[$monthy]=0;
		}
		$date=substr($line,2,10);
		
		if (!isset($days[$date])) {
			$pausestart=0;
			$days[$date]=array(
				'month'=>$monthy,
				'date'=>$date,
				'datestamp'=>strtotime($date),
				'start'=>substr($line,13, 8),
				'startstamp'=>$datetime,
				'pause'=>0,
			);
		} elseif (!$coming) {
			$pausestart=$datetime;
		} else {
			$days[$date]['pause']+=($datetime-$pausestart);
		}
		
		if ($date==date("Y-m-d")) $datetime=time();
		
		$days[$date]['end']=substr($line,13, 8);
		$days[$date]['endstamp']=$datetime;
		
		$worktime=$datetime-$days[$date]['startstamp'];
		$days[$date]['worktime']=$worktime;
		$solldiff=60*525;
		$days[$date]['diff']=$worktime-$solldiff;
		
		$olddate=$date;
	}
	fclose($f);
	
	$days[$date]['endstamp']=$datetime;
	
	//unset($days[$date]);
	
	foreach ($days as &$day) {
		$months[$day['month']]+=$day['diff']-$day['pause'];
		$day['monthdiff']=$months[$day['month']];
		$day['diff']=$day['diff']-$day['pause'];
		//$day['worktime']=$day['worktime']-$day['diff'];
	}
	
	$curmonth=date("Ym");
	if (isset($_GET['m'])) {
		$curmonth=$_GET['m'];
	}
	$vals=array();
	$valsdif=array();
	$daynames=array();
	$alt=true;
	//print_r($days);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Zeittabelle - wann komme ich, wann gehe ich?</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="">
	<link href="style.css" media="screen" rel="stylesheet" type="text/css">
</head>
<body>
<div id="month_navigator">
<div id="action_menu">
<a href="#" id="correct_action_link">Korrigieren</a>
<div id="correct_widow">
<form action="<?php echo $_SERVER["SCRIPT_NAME"] ?>" method="get">
Korrektur in Minuten:
<input type="text" id="correction_value" name="correction_value">
Korrigierte Differenz:
<input type="text" id="correction_absolute" name="correction_absolute">

<input type="submit">
</form>
</div>
</div>
<div>
&bull;
<?php foreach($months as $month=>$time): ?>
<a href="<?php echo $_SERVER["SCRIPT_NAME"] ?>?m=<?php echo $month ?>" class="<?php echo ($month==$curmonth ? 'active' : '') ?>"><?php echo formatmonth($month) ?></a> &bull;
<?php endforeach; ?>
</div>
</div>

<div id="overview">
	<div id="day_diff" class="">
		<span class="diff_label">Ankunft:</span><br>
		<?php echo date("G:i:s", $day['startstamp']) ?>
	</div>
	
	<div id="day_diff" class="">
		<span class="diff_label">Pause:</span><br>
		<?php echo gmdate("G:i:s", $day['pause']) ?>
	</div>
	
	<div id="day_diff" class="">
		<span class="diff_label">Feierabend:</span><br>
		<?php echo date("G:i:s", $day['startstamp']-$day['pause']+60*60*8.75) ?>
	</div>
	
	<?php if ($day['diff']>0): ?>
		<div id="day_diff">
			<span class="diff_label">Differenz:</span><br>
			<?php echo gmdate("G:i:s", $day['diff']) ?>
		</div>
	<?php else:?>
		<div id="day_diff" class="negative">
			<span class="diff_label">Differenz heute:</span><br>
			-<?php echo gmdate("G:i:s", $day['diff']*-1) ?>
		</div>
	<?php endif; ?>
	
	<?php if ($months[$curmonth]>0): ?>
		<div id="month_diff">
			<span class="diff_label"><?php echo formatmonth($curmonth) ?>:</span><br>
			<?php echo gmdate("G:i:s", $months[$curmonth]) ?>
		</div>
	<?php else:?>
		<div id="month_diff" class="negative">
			<span class="diff_label"><?php echo formatmonth($curmonth) ?>:</span><br>
			-<?php echo gmdate("G:i:s", $months[$curmonth]*-1) ?>
		</div>
	<?php endif; ?>
</div>


<div style="overflow:hidden" id="content_wrapper">
<div style="float:left">
<table>
<thead>
<tr>
<th>
	Datum
</th>
<th>
	Anwesenheit
</th>
<th>
	Länge
</th>
<th>
	Pause
</th>
<th>
	Diff.
</th>
<th>
	Gesamt
</th>
</tr>
</thead>
<tbody>
<?php foreach ($days as $cday): ?>
<?php if ($curmonth==$cday['month']): ?>
<?php 
	$bc=gmdate("i",$cday['worktime']-$cday['pause']);
	$part=$bc/60;
	$floattime=gmdate("G",$cday['worktime']-$cday['pause'])+$part;
	$vals[]=$floattime;
	$daynames[]=date("d.",$cday['datestamp']);
	
	$bc=gmdate("i",$cday['monthdiff']*-1);
	$part=$bc/60;
	
	if ($cday['monthdiff']>0) {
		$bc=gmdate("i",$cday['monthdiff']);
		$part=$bc/60;
		$floattime=gmdate("G",$cday['monthdiff'])+$part;
	} else {
		$bc=gmdate("i",$cday['monthdiff']*-1);
		$part=$bc/60;
		$floattime=(gmdate("G",$cday['monthdiff']*-1)+$part)*-1;
	}
	$valsdif[]=$floattime;
?>

<tr class="<?php echo ($alt=!$alt ? 'alt' : '') ?>">
	<td>
		<strong><?php echo date("d.m.Y",$cday['datestamp'])?></strong>
	</td>
	<td>
		<?php echo date("G:i",$cday['startstamp'])?> -<?php echo date("G:i",$cday['endstamp'])?>
	</td>
	<td>
		<?php echo gmdate("G:i",$cday['worktime']) ?>
	</td>
	<td>
	<?php echo gmdate("G:i",$cday['pause']) ?>
	</td>
	<td>
		<?php if ($cday['diff']>=0): ?>
		+<?php echo gmdate("G:i",$cday['diff']) ?>
		<?php else: ?>
		<span class="negative_value">-<?php echo gmdate("G:i",$cday['diff']*-1) ?></span>
		<?php endif; ?>
	</td>
	<td>
	<?php if ($cday['monthdiff']>=0): ?>
		+<?php echo gmdate("G:i",$cday['monthdiff']) ?>
	<?php else: ?>
		<span class="negative_value">-<?php echo gmdate("G:i",$cday['monthdiff']*-1) ?></span>
	<?php endif; ?>
		
	</td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div style="float:left;margin:0 0 0 20px;">
<img src="http://chart.apis.google.com/chart?chtt=Anwesenheit+in+Stunden&chs=450x180&chxt=y,x&chxl=0:|6:15|8:45|11:15|1:|<?php echo (join('|', $daynames)) ?>&chco=7097AE&cht=lc&chm=r,CAE8EA,0,0.49,0.51&chds=6.25,11.25&chd=t:<?php echo join(',',$vals) ?>">
<br>
<img src="http://chart.apis.google.com/chart?chtt=Differenz+zum+Soll&chs=450x180&chxt=y,x&chxl=0:|-3:00|-1:30|0:00|+1:30|+3:00|1:|<?php echo (join('|', $daynames)) ?>&chco=6694E3&cht=bvs&chp=0.5&chds=-3,3&chd=t:<?php echo join(',',$valsdif) ?>">
</div>
</div>
<div>
<strong>Hash:</strong> <?php echo $hash ?><br />
<a href="download.php?h=<?php echo $hash ?>">Daemon downloaden</a><br>
<a href="index.php">Ausloggen</a>
</div>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/mootools/1.2.1/mootools-yui-compressed.js"></script>
<script type="text/javascript">
$('correct_action_link').addEvent('click', function() {
		$('correct_widow').toggleClass('visible');
		$('correction_value').focus();
});
</script>
</body>
</html>

