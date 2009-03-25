<?php
	session_start();
	if (isset($_SESSION['userhash'])) {
		unset($_SESSION['userhash']);
	}
	
	if ($_GET['e']==1) {
		$error='Login fehlgeschlagen!';
	}
	
include "views/index.phtml";

