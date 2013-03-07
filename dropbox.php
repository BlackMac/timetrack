<?php
include "application/TTDropbox.php";
include "TimeTrack.class.php";
$timetrack = new TimeTrack();

$id = session_id();
if(empty($id)) {
	session_start();
}

$loggedin=false;
$loggedin = $timetrack->login($_POST['u'], $_POST['p'], $_GET['h']);
$hash = $timetrack->hash;

if(!$loggedin) {
	unset($hash);
	unset($_SESSION['userhash']);
}

if (!$loggedin) {
	if ($hash!="") {
		unset($_SESSION['userhash']);
		header("Location: download.php?h=".$hash);
		exit;
	} else {
		unset($_SESSION['userhash']);
		header("Location: index.php?e=1");
		exit;
	}
}

$options = $timetrack->getOptions();

$consumerKey = "v7o88y5e7ue41gz";
$consumerSecret = "pctm137te23evi3";

$requestTokenURL = "https://api.dropbox.com/1/oauth/request_token";
$getTokenURL = "https://api.dropbox.com/1/oauth/access_token";
$authorizationTokenURL = "https://www.dropbox.com/1/oauth/authorize";
$callbackURL = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/show.php?response=yes&dropbox=1';

if(!isset($options['dropbox']))
{
	if(!isset($_SESSION['dropbox_request_token']) || time()-$_SESSION['dropbox_request_token_received'] > 60)
	{
		$params = array(
			'oauth_consumer_key' => $consumerKey,
			'oauth_nonce' => md5(microtime(true)),
			'oauth_signature_method' => 'PLAINTEXT', // or 'HMAC-SHA1'
			'oauth_signature' => $consumerSecret . chr(38),
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0',
			'oauth_callback' => $callbackURL,
		);

		$response = file_get_contents($requestTokenURL . '?' . http_build_query($params));
		parse_str($response, $token);

		if(!isset($token['oauth_token_secret'], $token["oauth_token"])) {
			echo "<b>Dropbox: Failed to create request token. </b>";
			die();
		}

		$_SESSION['dropbox_request_token_received'] = time();
		$_SESSION['dropbox_request_token'] = json_encode($token);

	} else {
		$token = json_decode($_SESSION['dropbox_request_token'], true);
	}

	if(!isset($_SESSION['dropbox_access_token']) && !isset($_GET['response']))
	{
		$params = array(
			'oauth_token' => $token['oauth_token'],
			'oauth_callback' => $callbackURL
		);

		header('Location: ' . $authorizationTokenURL . '?' . http_build_query($params));
	} elseif(isset($_GET['response'])) {
		$params = array(
			'oauth_consumer_key' => $consumerKey,
			'oauth_nonce' => md5(microtime(true)),
			'oauth_signature_method' => 'PLAINTEXT', // or 'HMAC-SHA1'
			'oauth_signature' => $consumerSecret . chr(38) . $requestToken['oauth_token_secret'],
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0',
			'oauth_verifier' => $_GET['uid'],
			'oauth_token' => $_GET['oauth_token'],
		);

		$response = file_get_contents($getTokenURL . '?' . http_build_query($params));
		parse_str($response, $token);

		if(!isset($token['oauth_token_secret'], $token["oauth_token"])) {
			echo "<b>Dropbox: Failed to create access token. </b>";
			die();
		}

		// array ( 'oauth_token_secret' => 'yptm8tt3wh3dk9h', 'oauth_token' => 'pn0d2o3w32b2kkw', )
		$_SESSION['dropbox_access_token'] = json_encode($token);

		$options['dropbox'] = array(
			'oauth_token' => $token["oauth_token"],
			'oauth_token_secret' => $token["oauth_token_secret"],
		);

		$timetrack->setOptions($options);
	}
}

include "views/dropbox.phtml";

ob_implicit_flush(true);
ob_end_flush();

$dropbox = new TTDropbox($consumerKey, $consumerSecret, $options['dropbox']['oauth_token'], $options['dropbox']['oauth_token_secret']);

$dropboxFiles = getDropboxFiles();

$filesToUpload = array();	// Dateien, die hochgeladen werden müssen
$newDropboxFiles = array();	// Inhalt der neuen dropbox.ini
foreach ($timetrack->getAllDataFilesWithHash() as $absname => $fileInfo)
{
	if(!isset($dropboxFiles[$fileInfo['basename']]) || $dropboxFiles[$fileInfo['basename']] != $fileInfo['hash'])
	{
		$filesToUpload[] = $absname;
	}
	$newDropboxFiles[$fileInfo['basename']] = $fileInfo['hash'];
}
$dropbox->uploadFileFromString('dropbox.ini', json_encode($newDropboxFiles));

echo "<ul>";
foreach($filesToUpload as $file)
{
	$success = $dropbox->uploadFile($file);
	if($success === false) {
		echo "<li>'".basename($file)."': Fehler beim Hochladen!</li>";
		echo "<li><b>Der Vorgang wird komplett abgebrochen!</b></li>";
		break;
	} else {
		echo "<li>'".basename($file)."': Hochladen <b>erfolgreich</b>. Rückgabe: '<pre>".$success."</pre>'</li>";
	}

}
echo "<li>Fertig!</li>";
echo "</ul>";


function getDropboxFiles()
{
	$decodedDropboxFiles = array();
	$dropboxFiles = $dropbox->getFile('dropbox.ini');
	if($dropboxFiles !== false) {
		$decodedDropboxFiles = json_decode($dropboxFiles, true);
		if($decodedDropboxFiles === null) {
			echo "<b>dropbox.ini ist unbrauchbar</b><br>";
		}
	} else {
		echo "<b>Die Datei dropbox.ini kann nicht herunterladen werden.</b><br>";
	}
	return $decodedDropboxFiles;
}