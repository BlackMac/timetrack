<?php
header('Content-type: text/cache-manifest');
$path_info=pathinfo($_SERVER['REQUEST_URI']);

$url='http://'.$_SERVER['SERVER_NAME'].$path_info['dirname'].'/';
//echo '#'.microtime()."\n";
?>
CACHE MANIFEST
# v0.1
<?php echo $url; ?>style.css
<?php echo $url; ?>mobile.js
<?php echo $url; ?>mootools-yui-compressed.js

<?php echo $url; ?>../img/mobile/button_green.png
<?php echo $url; ?>../img/mobile/button_red.png
<?php echo $url; ?>../img/mobile/infogradient.png
<?php echo $url; ?>../img/mobile/topgradient.png
<?php echo $url; ?>../img/mobile/wait.gif.png