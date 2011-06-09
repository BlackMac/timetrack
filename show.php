<?php
include "functions_GRML.php";
include "TimeTrack.class.php";
include_once 'application/View.php';

session_start();

class Timetrack_View_Show extends Timetrack_View
{
	private $core;
	
	protected function prepare()
	{
		$curmonth = date("Ym");
		if (isset($_GET['m'])) {
			$curmonth = $_GET['m'];
		}
		$this->view->curmonth = $curmonth;
		
		$this->core = new TimeTrack();
				
		if (!$this->core->login($_POST['u'], $_POST['p'])) {
			if ($this->core->generateHash($_POST['u'], $_POST['p']) ) {
				unset($_SESSION['userhash']);
				header("Location: download.php?h=". $this->core->generateHash($_POST['u'], $_POST['p']) );
				exit;
			} else {
				unset($_SESSION['userhash']);
				header("Location: index.php?e=1");
				exit;
			}
		}
				
		$this->core->setMonth($curmonth);
		$this->view->hash = $this->core->hash;
		$this->view->isWritable = $this->core->isWritable();
		
		$this->view->data = $this->core->parseData();
		$this->view->day = $this->core->getLastDay();
		$this->view->alt = true;
		$this->view->presenceGraphUrl = $this->core->generatePresenceGraphUrl($curmonth, 'Anwesenheit in Stunden');		
		$this->view->differenceGraphUrl = $this->core->generateDifferenceGraphUrl($curmonth, 'Differenz zum Soll');
		
		$this->setViewScript('show');
		$this->view->mobiledevice = $this->detectMobileDevices();
		
		$options = $this->core->getOptions();
		$this->view->notificationsMapping = array(
			'when' => array(
				'5' => '5 Minuten',
				'10' => '10 Minuten',
				'15' => '15 Minuten',
				'20' => '20 Minuten',
				'25' => '25 Minuten',
				'30' => '30 Minuten',
			),
			'what' => array(
				'earliest' => 'frühestmöglichen',
				'normal' => 'normalen',				
			),
			'how' => array(
				'mail' => 'E-Mail',
				'sms' => 'SMS',
				'iphone' => 'iPhone-Push'
			),
		);
		$defaultNotification = array(
			'when' => $this->view->notificationsMapping['when']['5'],
			'what' => $this->view->notificationsMapping['what']['earliest'],
			'how' => $this->view->notificationsMapping['how']['mail'],
		);
		$this->view->notifications = array_merge($defaultNotification, (array)$options['notifications']); 
		$this->view->backup = $options['backup'];
	}
}

$page = new Timetrack_View_Show();
echo $page->render();