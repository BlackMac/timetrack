<?php

class TimeTrack {

	private $file;
	private $user;
	public $hash;
	private $loadedData = false;
	private $rawData;
	private $data = array();
	
	public function login($user = null, $pass = null, $hash = null)
	{
//		$hash = null;

		if (!isset($hash)) {
			if (isset($user) && isset($pass)) {
				$hash = md5($user."uphashseed".$pass);
			} elseif (isset($_SESSION['userhash'])) {
				$hash = $_SESSION['userhash'];
			}
		}
		
		if(!isset($hash)) {
			return false;
		}
		
		$auth = $this->setFile($hash.'.log');
		
		if(!$auth) {
			return false;
		}

		if (isset($user)) {
			$_SESSION['username'] = $user;
		} elseif (isset($_SESSION['username'])) {
			$user = $_SESSION['username'];
		}
		
		$this->user = $user;
		$this->hash = $hash;

		$_SESSION['userhash']=$hash;
		
		return true;
	}

	public function setFile($file) 
	{
		$fpath=realpath(dirname(__FILE__) . '/logs/'.$file);
		if (file_exists($fpath)) {
			$this->file = $fpath;
			return true;
		} else {
			return false;
		}
	}

	public function loadFile() 
	{
		$fileContent = file($this->file);
		$rawData = array();

		foreach ($fileContent as $line) {
			if(trim($line) != "") {
				$rawData[] = trim($line);
			}
		}
		$this->rawData = $rawData;

		$this->loadedData = true;
		
		return true;
	}

	public function updateFile($date, $oldtimestamp, $newtimestamp)
	{
		if(!$this->loadedData) $this->loadFile();

		$searchdate = date("Y-m-d\TH:i:s", $oldtimestamp);
		$replacedate =  date("Y-m-d\TH:i:s", $newtimestamp);
		foreach ($this->rawData as &$line) {
			if(false !== strpos($line, $searchdate)) {
				$line = str_replace($searchdate, $replacedate, $line);
				break;
			}
		}
		$this->parseData();

		@copy($this->file, $this->file . '.old');
		if(@file_put_contents($this->file, join("\r\n", $this->rawData)) === false)
		{
			return false;
		}

		return true;
	}
	
	public function parseData()
	{
		if(!$this->loadedData) $this->loadFile();
		
		$this->data = array('days' => array(),
							'months' => array(),
							);
							
		$pausestart = 0;

		foreach ($this->rawData as $line_num => $line) {
			if (substr($line,0,1)=="#") continue;
			$coming=(substr($line,0,1)=="+");
			
			$datetime=strtotime(substr($line,2,19));
			$monthy=date("Ym",$datetime);
			
			if (!isset($this->data['months'][$monthy])) {
				$this->data['months'][$monthy]=0;
			}
			$date=substr($line,2,10);
			
			if (!isset($this->data['days'][$date])) {
				$pausestart=0;
				$this->data['days'][$date]=array(
					'month'=>$monthy,
					'date'=>$date,
					'datestamp'=>strtotime($date),
					'start'=>substr($line,13, 8),
					'startstamp'=>$datetime,
					'laststateIn' => $coming,
					'pause'=>0,
				);
			} elseif (substr($line,0,1)=="C") {
				echo "C".$this->data['days'][$date]['start'];
				$this->data['days'][$date]['startstamp']-=substr($line,22);
				continue;
			} elseif (!$coming) {
				$this->data['days'][$date]['laststateIn'] = $coming;
				$pausestart=$datetime;
			} else {
				$this->data['days'][$date]['pause']+=($datetime-$pausestart);
			}
			
			if ($coming && $date==date("Y-m-d")) $datetime=time();
			
			$this->data['days'][$date]['end']=substr($line,13, 8);
			$this->data['days'][$date]['endstamp']=$datetime;
			
			$worktime=$datetime-$this->data['days'][$date]['startstamp'];
			$this->data['days'][$date]['worktime']=$worktime;
			$solldiff=60*525;
			$this->data['days'][$date]['diff']=$worktime-$solldiff;
			
			$olddate=$date;
		}
		
		foreach ($this->data['days'] as &$day) {
			$this->data['months'][$day['month']] += $day['diff'] - $day['pause'];
			$day['monthdiff'] = $this->data['months'][$day['month']];
			$day['diff'] = $day['diff'] - $day['pause'];	
			$this->data['daynames'][$day['month']][] = date("d.",$day['datestamp']);
			$daynames[] = date("d.",$day['datestamp']);
		}
		
		return $this->data;
	}

	public function getLastDay() {
		if(!isset($this->data) || !isset($this->data['days']))
			return array();
		else
			return end($this->data['days']);
	}

	public function generatePresenceGraphUrl($month, $title = 'Anwesenheit in Stunden')
	{
		$vals=array();
		
		foreach ($this->data['days'] as $day) {
			if ($month != $day['month']) continue;
			
			$bc=gmdate("i",$day['worktime']-$day['pause']);
			$part=$bc/60;
			$floattime=gmdate("G",$day['worktime']-$day['pause'])+$part;
			$vals[]=$floattime;
			
			$daynames[]=date("d.",$day['datestamp']);
		}
		
		$baseUrl = 'http://chart.apis.google.com/chart';
		
		$data = array(
					'chtt' => $title,
					'chs' => '450x180',
					'chxt' => 'y,x',
					'chxl' => '0:|6:15|8:45|11:15|1:|'. join('|', $this->data['daynames'][$month]),
					'chco' => '7097AE',
					'cht' => 'lc',
					'chm' => 'r,CAE8EA,0,0.49,0.51',
					'chds' => '6.25,11.25',
					'chd' => 't:' . join(',',$vals),
					);
		
		return $baseUrl . '?' . http_build_query($data);
	}
	
	public function generateDifferenceGraphUrl($month, $title = 'Differenz zum Soll')
	{
		$valsdif=array();
		
		foreach ($this->data['days'] as $day) {
			if ($month != $day['month']) continue;

			if ($day['monthdiff']>0) {
				$bc=gmdate("i",$day['monthdiff']);
				$part=$bc/60;
				$floattime=gmdate("G",$day['monthdiff'])+$part;
			} else {
				$bc=gmdate("i",$day['monthdiff']*-1);
				$part=$bc/60;
				$floattime=(gmdate("G",$day['monthdiff']*-1)+$part)*-1;
			}
			$valsdif[]=$floattime;
		}
		
		$baseUrl = 'http://chart.apis.google.com/chart';

		$data = array(
					'chtt' => $title,
					'chs' => '450x180',
					'chxt' => 'y,x',
					'chxl' => '0:|-3:00|-1:30|0:00|+1:30|+3:00|1:|'. join('|', $this->data['daynames'][$month]),
					'chbh' => 'a',
					'chco' => '6694E3',
					'cht' => 'bvs',
					'chp' => '0.5',
					'chds' => '-3,3',
					'chd' => 't:' . join(',',$valsdif),
					);
		
		return $baseUrl . '?' . http_build_query($data);	
	}
	
}
