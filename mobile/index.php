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
	<title>timeTrack</title>

	<link href="style.css" media="screen" rel="stylesheet" type="text/css">
	<style type="text/css" media="screen">@import "jqtouch/jqtouch/jqtouch.min.css";</style>
	<style type="text/css" media="screen">@import "jqtouch/themes/jqt/theme.min.css";</style>

	<script src="jqtouch/jqtouch/jquery.1.3.2.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="jqtouch/jqtouch/jqtouch.min.js" type="application/x-javascript" charset="utf-8"></script>

	<script type="text/javascript" src="mobile2.js"></script>
</head>
<body>
    <div id="home">
        <div class="toolbar">
            <h1>TimeTrack</h1>
			<?php if($loggedin) : ?>
            <a class="button slideup" href="#page2">Stats</a>
			<?php endif; ?>
        </div>

		<?php  if(isset($_POST['u']) && isset($_POST['p'])) : ?>
			<?php  if(!$loggedin) : ?>
				<ul class="edgetoedge" style="background-color: #c00;">
					<li style="color: #fff; text-align: center;">
						Logindaten nicht korrekt!
					</li>
				</ul>
			<?php else : ?>
				<div class="info">
					Foreverlink:
					<?php $link= 'http://'.$_SERVER['SERVER_NAME']. dirname($_SERVER['SCRIPT_NAME']) .'/?h=' . $hash; ?>
					<a href="<?php echo $link; ?>"><?php echo $link; ?></a>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if(!$loggedin) : ?>
		        <form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
		            <ul class="edit form">
		            	<li><input type="text" name="u" id="name" placeholder="Username" /></li>
		            	<li><input type="password" name="p" id="password" placeholder="Password" /></li>
		            </ul>
		           	<input type="submit" name="submit" style="margin: 10px auto; width: 90%" class="whiteButton"  value="Login" />
		        </form>

			</div>

		<?php else : ?>

			<?php
			$curmonth=date("Ym");
			if (isset($_GET['m'])) {
				$curmonth=$_GET['m'];
			}

			$timetrack->setMonth($curmonth);

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

			<form class="expressform" style="text-align: center; margin-bottom: 30px; padding-top: 40px;">
				<?php if(!$coming) : ?>

				<button class="come" name="d" value="in" id="change_button">Anmelden</button>
				<?php else: ?>
				<button class="go" name="d" value="out" id="change_button">Abmelden</button>
				<?php endif; ?>
				<input type="hidden" name="h" value="<?php echo $hash; ?>">
			</form>

			<div class="metal" style="position: absolute; bottom: 0px; width: 100%;">
				<ul style="text-align: center;">
					<!--
					<li>
						<?php echo date('d.m.Y'); ?>
					</li>
					-->
					<li>
						<?php
						echo 'am <span class="last_date">'.$date.'</span> um <span class="last_time">'.$time.'</span>';
						echo '<br><strong class="direction_action">'.($coming ? 'GEKOMMEN' : 'GEGANGEN').'</strong>';
						?>
					</li>
				</ul>
			</div>
		    </div>

			<div id="page2">
		        <div class="toolbar">
		            <h1>Stats</h1>
		            <a class="button back" href="#home">Home</a>
		            <a class="button slideup" href="#graphs">Graphs</a>
		        </div>

				<ul>
					<li style="color: #bbb;">
						<span class="diff_label">Ankunft:</span>
						<span style="float: right"><?php echo date("G:i:s", $lastEntry['startstamp']) ?></span>
					</li>
					<li style="color: #bbb;">
						<span class="diff_label">Pause:</span>
						<span style="float: right"><?php echo gmdate("G:i:s", $lastEntry['pause']) ?></span>
					</li>
					<li style="color: #bbb;">
						<span class="diff_label">Feierabend:</span>
						<span style="float: right"><?php echo date("G:i:s", $lastEntry['startstamp']+$lastEntry['pause']+60*60*8.75) ?></span>
					</li>
					<li style="color: #bbb;">
						<span class="diff_label">Differenz:</span>
						<span style="float: right">
							<?php if ($lastEntry['diff']>0): ?>
								<?php echo gmdate("G:i:s", $lastEntry['diff']) ?>
							<?php else:?>
								-<?php echo gmdate("G:i:s", $lastEntry['diff']*-1) ?>
							<?php endif; ?>
						</span>
					</li>
					<li style="color: #bbb;">
						<span class="monthdiff_label">Monatsbilanz:</span>
						<span style="float: right">
							<?php if ($lastEntry['monthdiff']>0): ?>
								<?php echo gmdate("G:i:s", $lastEntry['monthdiff']) ?>
							<?php else:?>
								-<?php echo gmdate("G:i:s", $lastEntry['monthdiff']*-1) ?>
							<?php endif; ?>
						</span>
					</li>
				</ul>
			</div>

			<div id="graphs">
		        <div class="toolbar" id="graphtoolbar">
		            <h1>Graphs</h1>
		            <a class="button back" href="#home">Home</a>
		        </div>

			</div>

			<script type="text/javascript">
				var presenceGraph = "<?php echo $timetrack->generatePresenceGraphUrl($lastEntry['month'], 'Anwesenheit in Stunden'); ?>";
				var differenceGraph = "<?php echo $timetrack->generateDifferenceGraphUrl($lastEntry['month'], 'Differenz zum Soll'); ?>";
			</script>

		<?php endif; ?>

</body>
</html>
