<?php

class TT_Action {

	private $post;
	private $session;
	private $hash;

	function __construct() {
		$this->post = $_POST;
		
		$this->startSession();
		$this->dispatch();
	}
	
	private function startSession() {
		session_start();
		$this->session = $_SESSION;
		
		if (isset($this->session['userhash'])) {
			$this->hash = $this->session['userhash'];
		} else {
			echo "No Hash in session given";
			exit;
		}
		
	}
	
	private function dispatch()
	{
		if (!isset($this->post) || count($this->post) <= 0) {
			echo "Wrong method";
			return;
		}
		
		if (!isset($this->post['action']) || empty($this->post['action'])) {
			echo "No action specified";
			return;
		}
		
		$method = $this->post['action'] . 'Action';
		if(method_exists($this, $method)) {
			$this->$method();
		} else {
			echo "Wrong action specified";
			return;
		}
		return;
	
	}

	private function changeuserdataAction()
	{
		if (!isset($this->post['changeuserdata_user'], $this->post['changeuserdata_pass'])) {
			echo __METHOD__ . ': Required fields are missing';
			return;
		}
		
		$newhash = md5($this->post['changeuserdata_user']."uphashseed".$this->post['changeuserdata_pass']);

		$foldpath = realpath('logs/'.$this->hash.'.log');
		$fnewpath = dirname($foldpath) . '/'.$newhash.'.log';

		$result = rename($foldpath, $fnewpath);
		if($result) {
			$_SESSION['userhash'] = $newhash;
			$this->redirect('show.php');
		} else {
			echo __METHOD__ . ': Error occurred during renaming.';
			return;
		}	
	}
	
	private function redirect($whereTo)
	{
		header("Location: " . dirname($_SERVER['SCRIPT_NAME']) . '/' . $whereTo);
	}
}

$action = new TT_Action();

