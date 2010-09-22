<?php
class TimeTrack
{

	private $file;

	private $user;

	public $hash;

	private $loadedData = false;

	private $rawData;

	private $data = array();

	private $curMonth;

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

		$raw = file($old, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
		if($res === false)
			return array(
				'error' => true,
				'where' => 'old file is not readable'
			);

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
			return -1;
		}

		/*
		if(self::$migrationMode === false)
		{
			$fpath .= '/tracks.log';
		}
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

		if(!is_dir($this->file))
			$dir = dirname($this->file);
		else
			$dir = $this->file;

		$filename = $dir . DIRECTORY_SEPARATOR . 'month_' . $this->curMonth . '.log';
		if(! is_file($filename))
		{
			return false;
		}

		$fileContent = file($filename, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

		if($fileContent === false) {
			return false;
		}

		$this->rawData = $fileContent;

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
		if(! $this->loadedData)
		{
			if(! $this->loadFile())
				return;
		}

		$this->data = array(
			'days' => array(),
			'months' => array()
		);

		$this->data['months'] = $this->findAllMonths();

		$pausestart = 0;

		foreach ($this->rawData as $line_num => $line)
		{
			if(substr($line, 0, 1) == "#")
				continue;
			$coming = (substr($line, 0, 1) == "+");

			$datetime = strtotime(substr($line, 2, 19));
			$monthy = date("Ym", $datetime);

			if(! isset($this->data['months'][$monthy]))
			{
				$this->data['months'][$monthy] = 0;
			}
			$date = substr($line, 2, 10);

			if(! isset($this->data['days'][$date]))
			{
				$pausestart = 0;
				$this->data['days'][$date] = array(
					'month' => $monthy,
					'date' => $date,
					'datestamp' => strtotime($date),
					'start' => substr($line, 13, 8),
					'startstamp' => $datetime,
					'laststateIn' => (int)$coming,
					'pause' => 0
				);
			}
			elseif(substr($line, 0, 1) == "C")
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

			$this->data['days'][$date]['end'] = substr($line, 13, 8);
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
		if(! isset($this->data) || ! isset($this->data['days']))
			return array();
		else
			return end($this->data['days']);
	}

	public function generatePresenceGraphUrl($month, $title = 'Anwesenheit in Stunden')
	{
		$vals = array();

		foreach ($this->data['days'] as $day)
		{
			if($month != $day['month'])
				continue;

			$bc = gmdate("i", $day['worktime'] - $day['pause']);
			$part = $bc / 60;
			$floattime = gmdate("G", $day['worktime'] - $day['pause']) + $part;
			$vals[] = $floattime;

			$daynames[] = date("d.", $day['datestamp']);
		}

		$baseUrl = 'http://chart.apis.google.com/chart';

		$data = array(
			'chtt' => $title,
			'chs' => '450x180',
			'chxt' => 'y,x',
			'chxl' => '0:|' . $min . '|8:45|' . $max . '|1:|' . join('|', $this->data['daynames'][$month]),
			'chco' => '7097AE',
			'cht' => 'lc',
			'chm' => 'r,CAE8EA,0,0.49,0.51',
			'chds' => '6.25,11.25',
			'chd' => 't:' . join(',', $vals)
		);

		return $baseUrl . '?' . http_build_query($data);
	}

	public function generateDifferenceGraphUrl($month, $title = 'Differenz zum Soll')
	{
		$valsdif = array();

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

}
