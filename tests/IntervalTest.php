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
		
		$this->assertContains(STAMP_2019, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testMinutes()
	{
		$interval = new \CF\Chrono\Interval();
		
		$interval->every(15, "minutes");
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 15*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testDays()
	{
		$interval = new \CF\Chrono\Interval();
		
		$interval->every(1, "day");
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 24*60*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testWeeks()
	{
		$interval = new \CF\Chrono\Interval();
		
		$interval->every(2, "weeks");
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 14*24*60*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testWeekdays()
	{
		$interval = new \CF\Chrono\Interval();
		
		$interval->on("mon");
		
		$values = [
			1546819200, 1547424000, 1548028800, 1548633600,
			1549238400, 1549843200, 1550448000, 1551052800,
			1551657600, 1552262400, 1552867200, 1553472000,
			1554076800, 1554681600, 1555286400, 1555891200,
			1556496000, 1557100800, 1557705600, 1558310400,
			1558915200, 1559520000, 1560124800, 1560729600,
			1561334400, 1561939200, 1562544000, 1563148800,
			1563753600, 1564358400, 1564963200, 1565568000,
			1566172800, 1566777600, 1567382400, 1567987200,
			1568592000, 1569196800, 1569801600, 1570406400,
			1571011200, 1571616000, 1572220800, 1572825600,
			1573430400, 1574035200, 1574640000, 1575244800,
			1575849600, 1576454400, 1577059200, 1577664000
		];
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
}
