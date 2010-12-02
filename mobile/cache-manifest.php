<?php
header('Content-type: text/cache-manifest');
$path_info=pathinfo($_SERVER['REQUEST_URI']);

$url='http://'.$_SERVER['SERVER_NAME'].$path_info['dirname'].'/';
$comment = "";
$filelist = "";

$files = array();

$ite=new RecursiveDirectoryIterator(".");
foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
	if(preg_match('/\.php$/', $filename) != 0) continue;
	$files[] = $filename;
}

$files[] = "../static/img/mobile/wait.gif";
//echo '#'.microtime()."\n";

foreach($files as $file) {
  if(!file_exists($file)) continue;
  $comment .= "#" . $file . " - size: " . filesize($file) . " - time: " . filectime($file) . "\n"; 
  $filelist .= $url . $file . "\n";
}
?>
CACHE MANIFEST
<?php echo $comment; ?>

<?php echo $filelist; ?>
