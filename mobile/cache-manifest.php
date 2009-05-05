<?php
header('Content-type: text/cache-manifest');
$path_info=pathinfo($_SERVER['REQUEST_URI']);

$url='http://'.$_SERVER['SERVER_NAME'].$path_info['dirname'].'/';
$comment = "";
$filelist = "";

$files = array(
    "style.css",
    "mobile.js",
    "mootools-yui-compressed.js",
    "../images/navigator_buttons.gif",
    "../img/mobile/button_green.png",
    "../img/mobile/button_red.png",
    "../img/mobile/infogradient.png",
    "../img/mobile/topgradient.png",
    "../img/mobile/wait.gif",
    );
//echo '#'.microtime()."\n";

foreach($files as $file) {
  if(!file_exists($file)) continue;
  $comment .= "#" . $file . " - size: " . filesize($file) . " - time: " . filectime($file) . "\n"; 
  $filelist .= $url . $file . "\n";
}
?>
CACHE MANIFEST
# v0.1
<?php echo $comment; ?>

<?php echo $filelist; ?>
