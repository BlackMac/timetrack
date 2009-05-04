<?php
require_once "TimeTrack.class.php";

class TimeTrackAPI {
	
	private $_timetrack;

	function __construct() {
		$this->_timetrack = new TimeTrack();
	}

	public function login($hash) {
		if(!isset($hash)) throw new Exception('No hash given.');

		$login_success = $this->_timetrack->login(null, null, $hash);
		if(!$login_success) throw new Exception('Wrong hash.');

		return $login_success;
	}

	public function log($hash, $direction, $logtime = null, $message = null) {
		$this->login($hash);

		$res = $this->_timetrack->logFile($direction, $logtime, $message);
		return $res;
	}

	public function system_listMethods() {
		$methods = array('login', 'log');
		return $methods;
	}

}
