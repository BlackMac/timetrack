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

<html lang="en"> <!-- manifest="cache-manifest.php"> -->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
  <title>time@work</title>
  <link href="style.css" media="screen" rel="stylesheet" type="text/css">
  <link rel="apple-touch-icon" href="/img/favicon.png">
  <link rel="apple-touch-startup-image" href="/img/startupimage.png">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
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
	
	<div id="infosection" style="position: relative">
		<div id="page1" class="active singlesection" style="">
			<h1 class="now_date">
			  <?php echo date('d.m.Y'); ?>
			</h1>
			<?php
			echo '<p>am <span class="last_date">'.$date.'</span> um <span class="last_time">'.$time.'</span>';
			echo '<br><strong class="direction_action">'.($coming ? 'GEKOMMEN' : 'GEGANGEN').'</strong></p>';
			?>
		</div>
		<a href="#" class="btn btnright" id="show_stats_link"><span>Stats &raquo;</span></a>

		<div id="page2" class="dontshow singlesection" style="padding: 10px;">
			<div id="overview">
				<div id="day_diff" class="">
					<span class="diff_label">Ankunft:</span><br>
					<?php echo date("G:i:s", $lastEntry['startstamp']) ?>
				</div>
				
				<div id="day_diff" class="">
					<span class="diff_label">Pause:</span><br>
					<?php echo gmdate("G:i:s", $lastEntry['pause']) ?>
				</div>
				
				<div id="day_diff" class="">
					<span class="diff_label">Feierabend:</span><br>
					<?php echo date("G:i:s", $lastEntry['startstamp']+$lastEntry['pause']+60*60*8.75) ?>
				</div>
				
				<?php if ($lastEntry['diff']>0): ?>
					<div id="day_diff">
						<span class="diff_label">Differenz:</span><br>
						<?php echo gmdate("G:i:s", $lastEntry['diff']) ?>
					</div>
				<?php else:?>
					<div id="day_diff" class="negative">
						<span class="diff_label">Differenz heute:</span><br>
						-<?php echo gmdate("G:i:s", $lastEntry['diff']*-1) ?>
					</div>
				<?php endif; ?>
			</div>	
		</div>			
		<a href="#" class="dontshow btn btnleft" id="show_home_link"><span>&laquo; Home</span></a>
	</div>

		
<?php endif; ?>

<script type="text/javascript" src="mootools-yui-compressed.js"></script>
<script type="text/javascript" src="mobile.js"></script>

</body>
</html>
