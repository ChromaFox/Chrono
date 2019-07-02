<?php

define("START_2019",	1546300800);
define("STAMP_2019",	1562098538);
define("END_2019",		1577836800);

use PHPUnit\Framework\TestCase;

final class IntervalTest extends TestCase
{
	public function testTimestamp()
	{
		$interval = new \CF\Chrono\Interval();
		$interval->onTimestamp(STAMP_2019);
		
		$this->assertContains(STAMP_2019, $interval->getIntervalsBetween(START_2019, END_2019));
	}
	
	public function testMinutes()
	{
		$interval = new \CF\Chrono\Interval();
		
		$interval->every(15, "minutes");
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 15*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getIntervalsBetween(START_2019, END_2019));
	}
	
	public function testDays()
	{
		$interval = new \CF\Chrono\Interval();
		
		$interval->every(1, "day");
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 24*60*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getIntervalsBetween(START_2019, END_2019));
	}
}
