<!DOCTYPE html>

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>untitled</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Stefan Lange-Hegermann">
	<!-- Date: 2009-12-02 -->
</head>
<body>
	<pre>
<?php
	$file=fopen('logs/githook.txt','w');
	fwrite($file, date("l dS of F Y h:i:s A")."\n");
	fclose($file);
	passthru('git pull');
?>
	</pre>
</body>
</html>
