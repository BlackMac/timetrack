<?php
define('TT_DIRECTION_OUT', '-');
define('TT_DIRECTION_IN', '+');


class TimeTrack {
	private $db;
	private $userId;
	
	function __construct() {
		if (!file_exists("logs/logs.sqlite")) {
			$this->db=new SQLiteDatabase("logs/logs.sqlite");
			$this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, username CHAR(255) UNIQUE, password CHAR(60), email CHAR(80));");
			$this->db->query("CREATE TABLE events (id INTEGER PRIMARY KEY, user_id INTEGER, event_date TIMESTAMP, direction INTEGER, comment CHAR(255));");
		} else {
			$this->db=new SQLiteDatabase("logs/logs.sqlite");
		}
	}
	
	public function login($username=null, $password=null, $hash=null) {
		$users=$this->db->query("SELECT id, username, password FROM users WHERE username = '$username'", SQLITE_ASSOC);
		if ($users->numRows()!=1) {
			return false;
		}
		$user=null;
		$user=$users->fetch();
		
		if ($user['password']!=$password) return false;
		
		$this->userId=$user['id'];
		return true;
	}
	
	public function addEvent($direction=TT_DIRECTION_OUT, $event_date='datetime(\'now\')') {
		$query="INSERT INTO events (user_id, event_date, direction) VALUES ('$this->userId', $event_date, '".TT_DIRECTION_OUT."')";
		$this->db->query($query);
	}
	
	public function lastEvent() {
		$query="SELECT event_date, direction FROM events WHERE user_id=='$this->userId' ORDER BY event_date DESC";
		$res=$this->db->query($query);
		return $res->fetch();
	}
	
	public function timeSinceLastEvent() {
		$le=$this->lastEvent();
		return strtotime($le['event_date']);
	}
	
	public function register($username=null, $password=null) {
		$this->db->query("INSERT INTO users (username, password) VALUES ('$username','$password')");
	}
}