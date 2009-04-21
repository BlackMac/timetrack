<?php
	session_start();
	include "../TimeTrack.class.php";
	$timetrack = new TimeTrack();
	
	$loggedin = $timetrack->login($_POST['u'], $_POST['p'], $_GET['h']);
	$hash = $timetrack->hash;

	if(!$loggedin) {
		unset($hash);
		unset($_SESSION['userhash']);
	}
?>

<!DOCTYPE html>

<html lang="en" manifest="cache-manifest.php">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
	<link rel="apple-touch-icon" href="/img/favicon.png"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
  <title>time@work</title>
  <link href="style.css" media="screen" rel="stylesheet" type="text/css">
</head>
<body>

<?php  if(isset($_POST['u']) && isset($_POST['p'])) : ?>
	<?php  if(!$loggedin) : ?>
		<p>Logindaten nicht korrekt!</p>
	<?php else : ?>
		<p>Sie können diese URL direkt zu ihren Bookmarks hinzufügen:<br>
		<?php $link= 'http://'.$_SERVER['SERVER_NAME']. dirname($_SERVER['SCRIPT_NAME']) .'/?h=' . $hash; ?>
		<a href="<?php echo $link; ?>"><?php echo $link; ?></a>
		</p>
	<?php endif; ?>
<?php endif; ?>

<?php if(!$loggedin) : ?>
	<form method="POST">
		<p>
		<label for="name">User:</label><br>
			<input type="text" name="u" id="name" />
		</p>
		<p>
		<label for="password">Pass:</label><br>
			<input type="password" name="p" id="password" />
		</p>
		<p>
			<input type="submit" value="login" />
		</p>
	</form>
<?php else : ?>
	
	<?php 
	$timetrack->parseData();
	$lastEntry = $timetrack->getLastDay();
	
	$coming = $lastEntry['laststateIn'];

	if($coming) {
		$date=date("d.m.Y", $lastEntry['startstamp']);
		$time=date("H:i",  $lastEntry['startstamp']);
	} else {
		$date=date("d.m.Y", $lastEntry['endstamp']);
		$time=date("H:i",  $lastEntry['endstamp']);
	}
	
	?>

	<div id="page1" class="active" style="position: absolute; width: 100%;">
		
		<form class="expressform">
			<?php if(!$coming) : ?>
			<button class="come" name="d" value="in" id="change_button">

			</button>
			<?php else: ?>
			<button class="go" name="d" value="out" id="change_button">

			</button>
			<?php endif; ?>
			<input type="hidden" name="h" value="<?php echo $hash; ?>">
		</form>
		<div id="infosection">
			<h1 class="now_date">
			  <?php echo date('d.m.Y'); ?>
			</h1>
			<?php
			echo '<p>am <span class="last_date">'.$date.'</span> um <span class="last_time">'.$time.'</span>';
			echo '<br><strong class="direction_action">'.($coming ? 'GEKOMMEN' : 'GEGANGEN').'</strong></p>';
			?>
		</div>

	</div>

	<div id="page2" class="dontshow" style="position: absolute; width: 100%;">
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
				<?php echo date("G:i:s", $day['startstamp']+$day['pause']+60*60*8.75) ?>
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
		</div>	
	</div>
	
	<div id="navigator">
		<div id="action_menu">
			<ul>
				<li>
					<a href="#" class="dontshow" id="show_home_link">&laquo; Home</a>
				</li>
				<li>
					<a href="#" id="show_stats_link">Stats &raquo;</a>
				</li>
			</ul>
		</div>
	</div>
		
<?php endif; ?>

<script type="text/javascript" src="mootools-yui-compressed.js"></script>
<script type="text/javascript" src="mobile.js"></script>

</body>
</html>
