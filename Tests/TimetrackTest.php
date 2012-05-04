<?php
include_once ("TimeTrack.class.php");

class Tests_TimetrackTest extends PHPUnit_Framework_TestCase{
	public function testTimeTrackHashIsNull () {
		$tt = new TimeTrack();
		$this->assertNull($tt->hash);
	}

	public function testTimeTrackGeneratesHashForUserAndPassword() {
		$tt = new TimeTrack();
		$this->assertEquals("4db3cfdd7744b80286b34f7fb0188d33", $tt->generateHash("username","password"));
	}
}
