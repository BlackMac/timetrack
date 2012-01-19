<?php

function walk_method(&$item) {
	$item = trim($item);
}

function filter_method($item) {
	return !empty($item);
}

class TimeTrack
{

	private $file;

	private $user;

	public $hash;

	private $loadedData = false;

	private $rawData;

	private $data = array();

	private $curMonth;

	private $dailyWorkTime = 8.75;

	public function generateHash($user = null, $pass = null)
	{
		if(isset($user) && isset($pass))
		{
			return md5($user . "uphashseed" . $pass);
		}

		return null;
	}

	public function login($user = null, $pass = null, $hash = null)
	{
		//		$hash = null;


		if(! isset($hash))
		{
			if(isset($user) && isset($pass))
			{
				$hash = $this->generateHash($user, $pass);
			}
			elseif(isset($_SESSION['userhash']))
			{
				$hash = $_SESSION['userhash'];
			}
		}

		if(! isset($hash))
		{
			return false;
		}

		$auth = $this->setFile($hash);

		if($auth === false)
		{
			return false;
		}

		if(isset($user))
		{
			$_SESSION['username'] = $user;
		}
		elseif(isset($_SESSION['username']))
		{
			$user = $_SESSION['username'];
		}

		$this->user = $user;
		$this->hash = $hash;

		$_SESSION['userhash'] = $hash;

		if($auth === -1)
		{
			header("Location: migration.php");
			die();
		}

		return true;
	}

	public function getRawData()
	{
		if(! $this->loadedData)
		{
			if(! $this->loadFile()) {
				die("can not load file");
				return;
			}
		}
		return $this->rawData;
	}

	public function isWritable()
	{
		return is_writable($this->file);
	}

	public static $migrationMode = false;

	public function migrateFileToDir($file)
	{
		$old = realpath(dirname(__FILE__) . '/logs/') . '/' . $file . '.log';
		$new = realpath(dirname(__FILE__) . '/logs/') . '/' . $file;

		if(is_file($old) === false)
			return array(
				'error' => true,
				'where' => 'old is no file -> ' . $old
			);
		if(is_writable(realpath(dirname(__FILE__) . '/logs/')) === false)
			return array(
				'error' => true,
				'where' => 'logs directory is not writeable'
			);

		if(! is_dir($new))
		{
			$res = mkdir($new);
			if($res === false)
				return array(
					'error' => true,
					'where' => 'mkdir'
				);
		}

		/*
		 * if(!is_file($new . '/tracks.log')) { $res = copy($old, $new .
		 * '/tracks.log'); if($res === false) return array('error' => true,
		 * 'where' => 'copy'); }
		 */

		$raw = file($old);

		// trim all elements
		array_walk($raw, "walk_method");
		// remove empty elements
		$raw = array_filter($raw, "filter_method");

		$monthArray = array();
		foreach ($raw as $line)
		{
			$matches = array();
			preg_match("/^([-\+#])\[(\d{4}-\d{2}-\d{2}\w\d{2}:\d{2}:\d{2})\]\s(.*)/", $line, $matches);
			if(! isset($matches) || ! is_array($matches) || count($matches) == 0)
				return array(
					'error' => true,
					'where' => 'conversion of file failed. could not split line.'
				);
			list ($match, $status, $datetime, $comment) = $matches;
			$monthy = date("Ym", strtotime($datetime));
			$monthArray[$monthy][] = $line;
		}

		if(! isset($monthArray) || ! is_array($monthArray) || count($monthArray) == 0)
			return array(
				'error' => true,
				'where' => 'monthArray is not set or empty'
			);
		foreach ($monthArray as $month => $monthData)
		{
			$res = file_put_contents($new . '/month_' . $month . '.log', join("\r\n", $monthData) . "\r\n");
			if($res === false)
				return array(
					'error' => true,
					'where' => 'Can not save month logs.'
				);
		}

		rename($old, $old . '.migr');

		return array(
			'error' => false
		);
	}

	public function setFile($file)
	{
		$fpath = realpath(dirname(__FILE__) . '/logs/' . $file);

		if($fpath === false)
		{
			$fpath = realpath(dirname(__FILE__) . '/logs/' . $file . '.log');
		}

		if(! is_dir($fpath) && is_file($fpath) && self::$migrationMode === false)
		{
			return - 1;
		}

		/*
		 * if(self::$migrationMode === false) { $fpath .= '/tracks.log'; }
		 */

		if(file_exists($fpath))
		{
			$this->file = $fpath;
			return true;
		}
		else
		{
			return false;
		}
	}

	public function loadFile()
	{
		if(! isset($this->curMonth))
		{
			return false;
		}

		if(! is_dir($this->file))
			$dir = dirname($this->file);
		else
			$dir = $this->file;

		$filename = $dir . DIRECTORY_SEPARATOR . 'month_' . $this->curMonth . '.log';
		if(! is_file($filename))
		{
			return false;
		}

		$fileContent = file($filename);

		$rawData = array();

		foreach ($fileContent as $line)
		{
			if(trim($line) != "")
			{
				$rawData[] = trim($line);
			}
		}
		$this->rawData = $rawData;

		$this->loadedData = true;

		return true;
	}

	public function logFile($direction = null, $logtime = null, $message = null)
	{
		if(! isset($logtime) || empty($logtime))
		{
			$logtime = date("Y-m-d\TH:i:s");
		}

		$action = "#";

		if($direction == "in")
			$action = "+";
		if($direction == "out")
			$action = "-";

		if(! isset($message) || empty($message))
		{
			$message = $direction;
		}

		$logline = $action . '[' . $logtime . '] ***' . $message . '***' . "\r\n";

		$file = fopen($this->file, 'a');
		fputs($file, $logline);
		fclose($file);

		return array(
			'action' => $action,
			'time' => $logtime,
			'message' => $message
		);
	}

	public function updateFile($oldtimestamp, $newtimestamp)
	{
		$month = date("Ym", $oldtimestamp);
		$this->setMonth($month);

		if(! $this->loadedData)
		{
			if(! $this->loadFile())
				return;
		}

		$searchdate = date("Y-m-d\TH:i:s", $oldtimestamp);
		$replacedate = date("Y-m-d\TH:i:s", $newtimestamp);
		foreach ($this->rawData as &$line)
		{
			if(false !== strpos($line, $searchdate))
			{
				$line = str_replace($searchdate, $replacedate, $line);
				break;
			}
		}
		$this->parseData();

		return $this->writeFile(join("\r\n", $this->rawData), $month);
	}

	public function writeFile($rawFile, $month)
	{
		$this->setMonth($month);
		if(! $this->loadedData)
		{
			if(! $this->loadFile())
				return;
		}

		$file =  $this->file . DIRECTORY_SEPARATOR . 'month_' . $month . '.log';

		if(@copy($file, $file . '.old') === false)
		{
			return false;
		}

		if(@file_put_contents($file, $rawFile . "\r\n") === false)
		{
			return false;
		}

		return true;
	}

	public function getOptions()
	{
		if(!is_dir($this->file))
			$dir = dirname($this->file);
		else
			$dir = $this->file;

		$filename = $dir . DIRECTORY_SEPARATOR . 'options.ini';
		if(! is_file($filename))
		{
			return array();
		}

		$fileContent = file_get_contents($filename);
		return json_decode($fileContent, true);
	}

	public function setOptions($options)
	{
		if(!is_dir($this->file))
			$dir = dirname($this->file);
		else
			$dir = $this->file;

		$filename = $dir . DIRECTORY_SEPARATOR . 'options.ini';

		return file_put_contents($filename, json_encode($options));
	}

	/**
	 * need it for backup tasks
	 */
	public function getAllDataFiles()
	{
		if(!is_dir($this->file))
			return array($this->file);
		else
			$dir = $this->file;

		return glob($dir . '/*');
	}

	public function findAllMonths()
	{
		if(!is_dir($this->file))
			$dir = dirname($this->file);
		else
			$dir = $this->file;
		$retval = array();
		$this->data['months'] = array();
		foreach (glob($dir . '/month_*.log') as $month)
		{
			$month = str_replace('month_', '', basename($month, '.log'));
			$retval[$month] = null;
		}
		return $retval;
	}

	public function parseData()
	{
		$this->data = array(
			'days' => array(),
			'months' => array()
		);

		$this->data['months'] = $this->findAllMonths();

		if(! $this->loadedData)
		{
			if(! $this->loadFile())
				return $this->data;
		}

		$pausestart = 0;

		foreach ($this->rawData as $line_num => $line)
		{
			$matches = array();
			if(!preg_match("/^([-\+#])\[(\d{4}-\d{2}-\d{2}\w\d{2}:\d{2}:\d{2})\]\s(.*)/", $line, $matches))
				continue;
			list ($match, $status, $datetime, $comment) = $matches;

			if($status == "#")
				continue;
			$coming = ($status == "+");

			$datetime = strtotime($datetime);
			$monthy = date("Ym", $datetime);

			if(! isset($this->data['months'][$monthy]))
			{
				$this->data['months'][$monthy] = 0;
			}
			$date = date('Y-m-d', $datetime);

			if(! isset($this->data['days'][$date]))
			{
				$pausestart = 0;
				$this->data['days'][$date] = array(
					'month' => $monthy,
					'date' => $date,
					'datestamp' => strtotime($date),
					'start' => date('G:i:s', $datetime),
					'startstamp' => $datetime,
					'laststateIn' => (int)$coming,
					'pause' => 0
				);
			}
			elseif($status == "C")
			{
				echo "C" . $this->data['days'][$date]['start'];
				$this->data['days'][$date]['startstamp'] -= substr($line, 22);
				continue;
			}
			elseif(! $coming)
			{
				$this->data['days'][$date]['laststateIn'] = $coming;
				$pausestart = $datetime;
			}
			else
			{
				$this->data['days'][$date]['pause'] += ($datetime - $pausestart);
			}
			$this->data['days'][$date]['laststateIn'] = $coming;
			if($coming && $date == date("Y-m-d"))
				$datetime = time();

			$this->data['days'][$date]['end'] = date('h:i:s', $datetime);
			$this->data['days'][$date]['endstamp'] = $datetime;

			$worktime = $datetime - $this->data['days'][$date]['startstamp'];
			$this->data['days'][$date]['worktime'] = $worktime;
			$solldiff = 60 * 525;
			$this->data['days'][$date]['diff'] = $worktime - $solldiff;

			$olddate = $date;
		}

		foreach ($this->data['days'] as &$day)
		{
			$this->data['months'][$day['month']] += $day['diff'] - $day['pause'];
			$day['monthdiff'] = $this->data['months'][$day['month']];
			$day['diff'] = $day['diff'] - $day['pause'];
			$this->data['daynames'][$day['month']][] = date("d.", $day['datestamp']);
			$daynames[] = date("d.", $day['datestamp']);
		}
		return $this->data;
	}

	public function setMonth($month)
	{
		$this->curMonth = $month;
	}

	public function getLastDay()
	{
		if(! isset($this->data) || ! isset($this->data['days']) || count($this->data['days']) == 0)
			return array();
		else
			return end($this->data['days']);
	}

	public function getNormalDayEnd()
	{
		$lastDay = $this->getLastDay();
		if(count($lastDay) == 0) return null;
		return $lastDay['startstamp'] + $lastDay['pause'] + 60*60 * $this->dailyWorkTime;
	}

	public function getEarliestDayEnd()
	{
		$lastDay = $this->getLastDay();
		if(count($lastDay) == 0) return null;
		$yesterday = array_pop($this->data['days']);
		if(isset($yesterday) && $yesterday['date'] == date("Y-m-d",time())) {
			$yesterday = array_pop($this->data['days']);
		}
		if(!isset($yesterday)) {
			$yesterday = 0;
		}
		$yesterdaydiff = $yesterday['monthdiff'];
		$end = $lastDay['startstamp'] + $lastDay['pause'] - $yesterdaydiff + 60*60 * $this->dailyWorkTime;
    return $end;
	}

	public function generatePresenceGraphUrl($month, $title = 'Anwesenheit in Stunden')
	{
		$vals = array();
		$max = 31500;
		$min = 31500;

		if(count($this->data['days']) < 1) {
			return '';
		}

		foreach ($this->data['days'] as $day)
		{
			if($month != $day['month'])
				continue;

			$worktimeWOPause = $day['worktime'] - $day['pause'];

			if($max < $worktimeWOPause)
			{
				$max = $worktimeWOPause;
			}

			if($min > $worktimeWOPause)
			{
				$min = $worktimeWOPause;
			}

			$bc = gmdate("i", $worktimeWOPause);
			$part = $bc / 60;
			$floattime = gmdate("G", $worktimeWOPause) + $part;
			$vals[] = $floattime;

			$daynames[] = date("d.", $day['datestamp']);
		}

		$baseUrl = 'http://chart.apis.google.com/chart';

		$data = array(
			'chtt' => $title,
			'chs' => '450x180',
			'chxt' => 'y,x',
			'chxl' => '0:|' . gmdate("G:i", $min) . '|8:45|' . gmdate("G:i", $max) . '|1:|' . join('|', $this->data['daynames'][$month]),
			'chco' => '7097AE',
			'cht' => 'lc',
			'chm' => 'r,CAE8EA,0,0.49,0.51',
			'chds' => $min/3600-0.01 . ',' .$max/3600,
			'chd' => 't:' . join(',', $vals)
		);


		return $baseUrl . '?' . http_build_query($data);
	}

	public function generateDifferenceGraphUrl($month, $title = 'Differenz zum Soll')
	{
		$valsdif = array();

		if(count($this->data['days']) < 1) {
			return '';
		}

		foreach ($this->data['days'] as $day)
		{
			if($month != $day['month'])
				continue;

			if($day['monthdiff'] > 0)
			{
				$bc = gmdate("i", $day['monthdiff']);
				$part = $bc / 60;
				$floattime = gmdate("G", $day['monthdiff']) + $part;
			}
			else
			{
				$bc = gmdate("i", $day['monthdiff'] * - 1);
				$part = $bc / 60;
				$floattime = (gmdate("G", $day['monthdiff'] * - 1) + $part) * - 1;
			}
			$valsdif[] = $floattime;
		}

		$baseUrl = 'http://chart.apis.google.com/chart';

		$data = array(
			'chtt' => $title,
			'chs' => '450x180',
			'chxt' => 'y,x',
			'chxl' => '0:|-3:00|-1:30|0:00|+1:30|+3:00|1:|' . join('|', $this->data['daynames'][$month]),
			'chbh' => 'a',
			'chco' => '6694E3',
			'cht' => 'bvs',
			'chp' => '0.5',
			'chds' => '-3,3',
			'chd' => 't:' . join(',', $valsdif)
		);

		return $baseUrl . '?' . http_build_query($data);
	}

	private function _log($data)
	{
		echo "<pre>";
		var_export($data);
		echo "</pre>";
	}

	public function getEaster($iYear = -1)
	{
		// the Golden number
		$iGolden = ($iYear % 19) + 1;

		// the "Domincal number"
		$iDom = ($iYear + (int)($iYear / 4) - (int)($iYear / 100) + (int)($iYear / 400)) % 7;
		if($iDom < 0)
			$iDom += 7;

		// the solar and lunar corrections
		$iSolar = ($iYear - 1600) / 100 - ($iYear - 1600) / 400;
		$iLunar = ((($iYear - 1400) / 100) * 8) / 25;

		// uncorrected date of the Paschal full moon
		$iPFM = (3 - (11 * $iGolden) + $iSolar - $iLunar) % 30;
		if($iPFM < 0)
			$iPFM += 30;

		// corrected date of the Paschal full moon
		// days after 21st March
		if(($iPFM == 29) || ($iPFM == 28 && $iGolden > 11))
		{
			$iPFM--;
		}
		$iTMP = (4 - $iPFM - $iDom) % 7;
		if($iTMP < 0)
			$iTMP += 7;

		// Easter as the number of days after 21st March */
		$iEaster = $iPFM + $iTMP + 1;
		if($iEaster < 11)
		{
			$iMonth = 3;
			$iDay = $iEaster + 21;
		}
		else
		{
			$iMonth = 4;
			$iDay = $iEaster - 10;
		}
		$iEaster = mktime(0, 0, 0, $iMonth, $iDay, $iYear, - 1);
		return $iEaster;
	}

	private function getFirstAdvent($iYear)
	{
		$iFirstAdvent = mktime(0, 0, 0, 11, 26, $iYear);
		while (0 != date('w', $iFirstAdvent))
			$iFirstAdvent += 86400;
		return $iFirstAdvent;
	}

	public function getHolidays($iYear)
	{
		// Feste Feiertage short / long description
		$aHoliday[mktime(0, 0, 0, 1, 1, $iYear)] = 'Neujahr';
		// $aHoliday[mktime(0, 0, 0, 1, 6, $iYear)] = 'Heilige 3 K&oouml;nige';
		$aHoliday[mktime(0, 0, 0, 5, 1, $iYear)] = 'Tag der Arbeit';
		// $aHoliday[mktime(0, 0, 0, 8, 15, $iYear)] = 'Maria Himmelfahrt';
		$aHoliday[mktime(0, 0, 0, 10, 3, $iYear)] = 'Tag der deutschen Einheit';
		// $aHoliday[mktime(0, 0, 0, 10, 31, $iYear)] = 'Reformationstag';
		$aHoliday[mktime(0, 0, 0, 11, 1, $iYear)] = 'Allerheiligen';
		$aHoliday[mktime(0, 0, 0, 12, 24, $iYear)] = 'Heiligabend';
		$aHoliday[mktime(0, 0, 0, 12, 25, $iYear)] = '1. Weihnachtsfeiertag';
		$aHoliday[mktime(0, 0, 0, 12, 26, $iYear)] = '2. Weihnachtsfeiertag';
		$aHoliday[mktime(0, 0, 0, 12, 31, $iYear)] = 'Silvester';

		// Bewegliche Feiertage, von Ostern abhängig
		$iEaster = $this->getEaster($iYear);
		$iEasterDay = date('d', $iEaster);
		$iEasterMonth = date('m', $iEaster);
		$iEasterYear = date('Y', $iEaster);

		// $aHoliday[mktime(0,0,0,$iEasterMonth,$iEasterDay-48,$iEasterYear)]=
		// 'Rosenmontag';
		// $aHoliday[mktime(0,0,0,$iEasterMonth,$iEasterDay-46,$iEasterYear)]=
		// 'Aschermittwoch';
		$aHoliday[mktime(0, 0, 0, $iEasterMonth, $iEasterDay - 2, $iEasterYear)] = 'Karfreitag';
		// $aHoliday[mktime(0,0,0,$iEasterMonth,$iEasterDay,$iEasterYear)] =
		// 'Ostersonntag';
		$aHoliday[mktime(0, 0, 0, $iEasterMonth, $iEasterDay + 1, $iEasterYear)] = 'Ostermontag';
		$aHoliday[mktime(0, 0, 0, $iEasterMonth, $iEasterDay + 39, $iEasterYear)] = 'Himmelfahrt';
		// $aHoliday[mktime(0,0,0,$iEasterMonth,$iEasterDay+49,$iEasterYear)]=
		// 'Pfingstsonntag';
		$aHoliday[mktime(0, 0, 0, $iEasterMonth, $iEasterDay + 50, $iEasterYear)] = 'Pfingstmontag';
		$aHoliday[mktime(0, 0, 0, $iEasterMonth, $iEasterDay + 60, $iEasterYear)] = 'Fronleichnam';

		// Bewegliche Feiertage, vom ersten Advent abhängig
		// $iFirstAdvent = $this->getFirstAdvent($iYear);
		// $iAdventDay = date('d',$iFirstAdvent);
		// $iAdventMonth = date('m',$iFirstAdvent);
		// $iAdventYear = date('Y',$iFirstAdvent);

		// $aHoliday[mktime(0,0,0,$iAdventMonth,$iAdventDay,$iAdventYear)]= '1.
		// Advent';
		// $aHoliday[mktime(0,0,0,$iAdventMonth,$iAdventDay+7,$iAdventYear)]=
		// '2. Advent';
		// $aHoliday[mktime(0,0,0,$iAdventMonth,$iAdventDay+14,$iAdventYear)]=
		// '3. Advent';
		// $aHoliday[mktime(0,0,0,$iAdventMonth,$iAdventDay+21,$iAdventYear)]=
		// '4. Advent';
		// $aHoliday[mktime(0,0,0,$iAdventMonth,$iAdventDay-32,$iAdventYear)]=
		// 'Buss- und Bettag';
		// $aHoliday[mktime(0,0,0,$iAdventMonth,$iAdventDay-28,$iAdventYear)]=
		// 'Totensonntag';
		// $aHoliday[mktime(0,0,0,$iAdventMonth,$iAdventDay-35,$iAdventYear)]=
		// 'Volkstrauertag';

		return $aHoliday;
	}

	public function getDaySubjectsForYear($year)
	{
		if(!preg_match('/^\d{4}$/', $year))
		{
			return array();
		}

		if(!is_dir($this->file))
			$dir = dirname($this->file);
		else
			$dir = $this->file;

		$filename = $dir . DIRECTORY_SEPARATOR . 'subjects_' .$year.'.log';
		if(! is_file($filename))
		{
			return array();
		}

		$fileContent = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$returnValue = array();
		foreach($fileContent as $line) {
			$matches = array();
			if(preg_match('/\[(\d{4}-\d{2}-\d{2})\]\s(.*)/', $line, $matches))
			{
				$returnValue[$matches[1]] = array('subject' => $matches[2]);
			}
		}
		return $returnValue;
	}

	public function setDaySubjectsForYear($year, $subjects)
	{
		if(!preg_match('/^\d{4}$/', $year))
		{
			return false;
		}

		if(!is_dir($this->file))
			$dir = dirname($this->file);
		else
			$dir = $this->file;

		$filename = $dir . DIRECTORY_SEPARATOR . 'subjects_' .$year.'.log';

		$lines = array();
		foreach($subjects as $date => $payload)
		{
			$lines[] = sprintf("[%s] %s", $date, $payload['subject']);
		}

		$res = file_put_contents($filename, join("\r\n", $lines) . "\r\n");

		return $res !== FALSE;
	}

	public function changeDaySubject($date, $subject)
	{
		$dateSplitted = array();
		if(!isset($date, $subject)
			|| !in_array($subject, array('illness', 'vacation'))
			|| !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $dateSplitted)
		)
		{
			return false;
		}

		$actualSubjects = $this->getDaySubjectsForYear($dateSplitted[1]);
		$actualSubjects[$date] = array('subject' => $subject);

		return $this->setDaySubjectsForYear($dateSplitted[1], $actualSubjects);
	}
}
