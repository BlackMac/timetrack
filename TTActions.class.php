<?php

require_once ("TimeTrackSQL.class.php");

class TTActions {
	private $ttrack;
	
	function __construct() {
		session_start();
		$this->ttrack= new TimeTrack();
	}
	
	function loginAction() {
		if (isset($_SESSION['username'])) {
			return array('view'=>'login_success');
		}
		
		if (!isset($_POST['username'])) {
			return array('view'=>'login_form');
		} else {
			if ($this->ttrack->login($_POST['username'], $_POST['password'])) {
				
				$_SESSION['username']=$_POST['username'];
				//$_SESSION['user']=
				redirect('list');
			}
			return array('view'=>'login_form');
		}
	}
	
	function logoutAction() {
		unset($_SESSION['username']);
		redirect('login');
	}
	
	function registerAction() {
		return 'register';
	}
	
	function importAction() {
		if (!isset($_SESSION['username'])) redirect('login');
		return null;
	}
	
	function listAction() {
		if (!isset($_SESSION['username'])) redirect('login');
		$events=$this->ttrack->getEvents();
		return array('view'=>'login_success', 'events'=>$events);;
	}
}