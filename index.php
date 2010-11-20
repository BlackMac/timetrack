<?php
	session_start();
	if (isset($_SESSION['userhash'])) {
		unset($_SESSION['userhash']);
	}
	
	if ($_GET['e']==1) {
		$error='Login fehlgeschlagen!';
	}

include_once 'application/View.php';

class Timetrack_View_Index extends Timetrack_View
{
	protected function prepare()
	{
		$this->setViewScript('index');
		$this->view->mobiledevice = $this->detectMobileDevices();
	}
}

$page = new Timetrack_View_Index();
echo $page->render();
