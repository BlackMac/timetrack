<?php
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

$requestTokenURL = "https://api.dropbox.com/0/oauth/request_token";
$getTokenURL = "https://api.dropbox.com/0/oauth/access_token";
$authorizationTokenURL = "https://www.dropbox.com/0/oauth/authorize";
$fileURL = "https://api-content.dropbox.com/0/files/sandbox";
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

	echo "<ul>";
	foreach($timetrack->getAllDataFiles() as $file)
	{
		$success = $dropbox->uploadFile($file);
		if($success === false) {
			echo "<li>error while uploading '" . basename($file) . "' <br><b>I've got to stop all following operations!</b></li>";
			break;
		} else {
			echo "<li>upload of '" . basename($file) . "' <b>succeeded</b> with '".$success."'</li>";
		}

	}
	echo "<li>Done!</li>";
	echo "</ul>";


class TTDropbox
{
	private $consumerKey;
	private $consumerSecret;
	private $token;
	private $tokenSecret;
    private $boundary = 'R50hrfBj5JYyfR3vF3wR96GPCC9Fd2q2pVMERvEaOE3D8LZTgLLbRpNwXek3';
    private $oauthParams = array();
    private $queryParams = array();
    private $headers = array();
	private $baseUrl = 'https://api-content.dropbox.com/0/files/sandbox';

	public function __construct($consumerKey, $consumerSecret, $token, $tokenSecret) {
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;
		$this->token = $token;
		$this->tokenSecret = $tokenSecret;
	}

	public function uploadFile($file) {
		$filename = basename($file);
		$this->queryParams['file'] = $filename;
		$this->fileContent = file_get_contents($file);
		return $this->_sendFile();
	}

	private function _buildOAuthParams() {
		$this->oauthParams = array(
			'oauth_consumer_key' => $this->consumerKey,
			'oauth_nonce' => md5(microtime(true)),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => time(),
			'oauth_token' => $this->token,
			'oauth_version' => '1.0',
		);
		return $this->oauthParams;
	}

	private function _buildBaseString() {
		$this->basestring = join('&', array(
			'POST',
			urlencode($this->baseUrl),
			urlencode(http_build_query($this->queryParams + $this->oauthParams))
		));
		return $this->basestring;
	}

	private function _sign()
	{
		$key = urlencode($this->consumerSecret).'&'.urlencode($this->tokenSecret);
		$signature = base64_encode(hash_hmac("sha1", $this->basestring, $key, true));
		$this->oauthParams['oauth_signature'] = urlencode($signature);
		return $this->oauthParams['oauth_signature'];
	}

	private function _buildHeaders()
	{
		$tmp = array();
		foreach ($this->oauthParams as $key => $value) $tmp[] = $key.'=' . '"' . $value . '"';

		$this->headers = array(
            'Content-Type: multipart/form-data; boundary=' . $this->boundary,
			'Authorization: OAuth realm="",' . join(',',$tmp)
        );
        return $this->headers;
	}

	private function _buildBody()
	{
		$body="--" . $this->boundary . "\r\n";
        $body.="Content-Disposition: form-data; name=file; filename=".$this->queryParams['file']."\r\n";
        $body.="Content-type: application/octet-stream\r\n";
        $body.="\r\n";
        $body.=$this->fileContent;
        $body.="\r\n";
        $body.="--" . $this->boundary . "--";
        return $body;
	}

	private function _sendFile()
	{
		$this->_buildOAuthParams();
		$this->_buildBaseString();
		$this->_sign();
 		$ch = curl_init($this->baseUrl . '?' . http_build_query($this->queryParams) );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_buildHeaders());
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildBody());
		return curl_exec($ch);
	}

}
