<?php
	$file=fopen('logs/githook.txt','w');
	fwrite($file, date("l dS of F Y h:i:s A")."\n");
	fclose($file);
	exec('git pull');
?>