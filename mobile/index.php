<?php
	session_start();
	$hash = null;
	if (isset($_GET['h'])) {
		$hash = $_GET['h'];
	} elseif (isset($_SESSION['userhash'])) {
		$hash=$_SESSION['userhash'];
	} elseif (isset($_POST['u']) && isset($_POST['p'])) {
		$hash=md5($_POST['u']."uphashseed".$_POST['p']);
	}

	$loggedin = false;
	$fpath=realpath('../logs/'.$hash.'.log');
	if (file_exists($fpath)) $loggedin=true;

	if($loggedin) {
		$_SESSION['userhash']=$hash;
	} else {
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

<?php if(!isset($hash)) : ?>
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
	$allLines = file($fpath);
	$line = '';
	for($i = count($allLines)-1; $i >= 0; $i--) {
		if(substr($allLines[$i], 0, 1) == '#') continue;
		$line = trim($allLines[$i]);
		break;
	}
	if(!empty($line)) {
	$coming=(substr($line,0,1)=="+");
	
	$datetime=strtotime(substr($line,2,19))+60*60;
	$date=date("d.m.Y",$datetime);
	$time=date("H:i",$datetime);
	}
	?>

	<form class="expressform">
		<?php if(!$coming) : ?>
		<button class="come" name="d" value="in">

		</button>
		<?php else: ?>
		<button class="go" name="d" value="out">

		</button>
		<?php endif; ?>
		<input type="hidden" name="h" value="<?php echo $hash; ?>">
	</form>
	<div id="infosection">
		<h1>
		  <?php echo date('d.m.Y'); ?>
		</h1>
		<?php
		echo '<p>am '.$date.' um '.$time.'<br><strong>'.($coming ? 'GEKOMMEN' : 'GEGANGEN').'</strong></p>';
		?>
	</div>
<?php endif; ?>

<script type="text/javascript" src="mootools-yui-compressed.js"></script>
<script type="text/javascript" src="mobile.js"></script>

</body>
</html>
