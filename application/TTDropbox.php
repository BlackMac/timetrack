<?php
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
	private $baseUrl = '';
	private $method = "";

	private $urls = array(
		'fileupload' => 'https://api-content.dropbox.com/1/files/sandbox',
		'filedownload' => 'https://api-content.dropbox.com/1/files/sandbox',
		'metadata' => 'https://api.dropbox.com/1/metadata/sandbox'
	);

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
		$this->baseUrl = $this->urls['fileupload'];
		$this->method = "POST";
		return $this->_sendFile();
	}

	public function uploadFileFromString($filename, $content) {
		$this->queryParams['file'] = $filename;
		$this->fileContent = $content;
		$this->baseUrl = $this->urls['fileupload'];
		$this->method = "POST";
		return $this->_sendFile();
	}

	public function getMetadata()
	{
		$this->queryParams = array();
		$this->baseUrl = $this->urls['metadata'];
		$this->method = "GET";
		$ch = $this->_buildPreparedCurl();
		return curl_exec($ch);
	}

	public function getFile($file)
	{
		$this->queryParams = array();
		$this->baseUrl = $this->urls['filedownload'] . '/' . $file;
		$this->method = "GET";
		$ch = $this->_buildPreparedCurl();
		return curl_exec($ch);
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
			$this->method,
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

	private function _buildPreparedCurl()
	{
		$this->_buildOAuthParams();
		$this->_buildBaseString();
		$this->_sign();
		$ch = curl_init($this->baseUrl . '?' . http_build_query($this->queryParams) );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_buildHeaders());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($this->method == "POST")
		{
			curl_setopt($ch, CURLOPT_POST, true);
		}
		return $ch;
	}

	private function _sendFile()
	{
		$ch = $this->_buildPreparedCurl();
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildBody());
		return curl_exec($ch);
	}

}
