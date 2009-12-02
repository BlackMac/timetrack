<!DOCTYPE html>

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>GitHook</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Stefan Lange-Hegermann">
	<!-- Date: 2009-12-02 -->
</head>
<body>
	<pre>
<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	$file=fopen('logs/githook.txt','w');
	fwrite($file, date("l dS of F Y h:i:s A")."\n");
	fclose($file);
	system('cd /var/www/timetrack');
	system('/usr/bin/git pull');
?>
	</pre>
</body>
</html>
