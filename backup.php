<?php
include "TimeTrack.class.php";
include_once 'application/View.php';

session_start();

class Timetrack_View_Backup extends Timetrack_View
{
	private $core;

	protected function prepare()
	{
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
		
		$options = $this->core->getOptions();

		$this->setViewScript('backup');
	}
}

$page = new Timetrack_View_Backup();
echo $page->render();