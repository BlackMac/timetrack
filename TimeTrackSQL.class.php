<?php

class TimeTrack {
	function __construct($username=null, $password=null, $hash=null) {
		$db=null;
		if (!file_exists("logs/logs.sqlite")) {
			$db=new SQLiteDatabase("logs/logs.sqlite");
			$db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, username CHAR(255), password CHAR(60), email CHAR(80));");
			$db->query("CREATE TABLE events (id INTEGER PRIMARY KEY, user_id INTEGER, direction INTEGER, comment CHAR(255));");
		} else {
			$db=new SQLiteDatabase("logs/logs.sqlite");
		}
		
		$user=$db->singleQuery("SELECT * FROM users WHERE username = '$username'", true);
		
		if (!$user) {
			$db->query("INSERT INTO users (username, password) VALUES ('$username','$password')");
		}
		print_r($user);
	}
}