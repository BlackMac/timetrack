<?php
require_once "TimeTrack.class.php";

class TimeTrackAPI {
	
	private $_timetrack;

	function __construct() {
		$this->_timetrack = new TimeTrack();
	}

	public function login($params) {
		$hash = $params['hash'];
		if(!isset($hash)) throw new Exception('No hash given.');

		$login_success = $this->_timetrack->login(null, null, $hash);
		if(!$login_success) throw new Exception('Wrong hash.');

		return $login_success;
	}

	public function log($params) {
		$hash = $params['hash'];
		$direction = $params['direction'];
		$logtime = $params['logtime'];
		$message = $params['message'];

		$this->login(array('hash' => $hash));

		$res = $this->_timetrack->logFile($direction, $logtime, $message);
		return $res;
	}

	public function updateTimestamp($params) {
		$hash = $params['hash'];
		$old = $params['oldTimestamp'];
		$new = $params['newTimestamp'];

		if(!isset($old, $new)) {
			throw new Exception('Timestamp/s is missing.');
		}

		$this->login(array('hash' => $hash));

		$res = $this->_timetrack->updateFile($old, $new);
		return $res;
	}

	public function getMonth($params) {
		$hash = $params['hash'];
		$month = $params['month'];
		$year = $params['year'];

		$this->login(array('hash' => $hash));

		if(!isset($month)) $month = date('m');
		if(!isset($year)) $year = date('Y');
		$needle = $year.'-'.$month;

		$res = $this->_timetrack->parseData();
		foreach($res['days'] as $day => $day_data) {
			if(strpos($day, $needle) === false) {
				unset($res['days'][$day]);
			}
		}
		return $res['days'];
	}

	public function getLastDay($params) {
		$hash = $params['hash'];

		$this->login(array('hash' => $hash));

		$this->_timetrack->setMonth(date('Ym'));
		$this->_timetrack->parseData();
		$res = 	$this->_timetrack->getLastDay();

		return $res;
	}
	
	public function generateGraphUrls($params) {
		$hash = $params['hash'];

		$this->login(array('hash' => $hash));

		$this->_timetrack->setMonth(date('Ym'));
		$this->_timetrack->parseData();
		
		$res = array(
			'presence' => $this->_timetrack->generatePresenceGraphUrl(date('Ym'), 'Anwesenheit in Stunden'),
			'difference' => $this->_timetrack->generateDifferenceGraphUrl(date('Ym'), 'Differenz zum Soll')
		);
		
		return $res;
	}

	public function system_listMethods() {
		$methods = array(
				'getLastDay',
				'getMonth',
				'login',
				'log',
				'updateTimestamp',
				);
		return $methods;
	}

	public function updateNotification($params) {
		$hash = $params['hash'];

		$this->login(array('hash' => $hash));
		
		$possibleOptions = array(
			'enabled' => array(true, false),
			'when' => array('5', '10', '15', '20', '25', '30'),
			'what' => array('earliest', 'normal'),
			'how' => array('mail', 'sms', 'iphone'),
		);
		
		// check for right values
		if(!isset($params['option'], $params['value']) 
			|| !in_array($params['option'], array_keys($possibleOptions))
			|| !in_array($params['value'], $possibleOptions[$params['option']])
		) {
			return array("save" => "fail");
		}
		
		$options = $this->_timetrack->getOptions();
		$options['notifications'][$params['option']] = $params['value'];
		$this->_timetrack->setOptions($options);

		return array("save" => "ok");
	}
}
