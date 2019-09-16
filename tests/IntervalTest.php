<?php

define("START_2019",	1546300800);
define("STAMP_2019",	1562098500);
define("END_2019",		1577836800);
define("OFFSET_413_AM",	15180);

use PHPUnit\Framework\TestCase;

final class IntervalTest extends TestCase
{
	public function testTimestamp()
	{
		$interval = new \CF\Chrono\Interval("@".STAMP_2019);
		
		$this->assertEquals([STAMP_2019], $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testMinutes()
	{
		$interval = new \CF\Chrono\Interval("~15");
		$interval->starting(START_2019);
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 15*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testDays()
	{
		$interval = new \CF\Chrono\Interval();
		$interval->starting(START_2019);
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 24*60*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testAlternateDays()
	{
		$interval = new \CF\Chrono\Interval("}2");
		$interval->starting(START_2019);
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 2*24*60*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testWeeks()
	{
		$interval = new \CF\Chrono\Interval("*2");
		$interval->starting(START_2019);
		
		$values = [];
		for($i = START_2019; $i <= END_2019; $i += 14*24*60*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testWeekdays()
	{
		$interval = new \CF\Chrono\Interval("mon");
		
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
	
	public function testOrdinalOfWeekday()
	{
		$interval = new \CF\Chrono\Interval("mon;#2");
		
		$values = [
			1554076800, 1554681600, 1555286400, 1555891200,
			1556496000, 1561939200, 1562544000, 1563148800,
			1563753600, 1564358400
		];
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testDaysAtASpecificTime()
	{
		$interval = new \CF\Chrono\Interval("daily;@4:13 AM");
		
		$values = [];
		for($i = START_2019+OFFSET_413_AM; $i <= END_2019; $i += 24*60*60)
			$values []= $i;
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testSpecificDay()
	{
		$interval = new \CF\Chrono\Interval("7/02;@8:15 PM");
		
		$values = [STAMP_2019];
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testMonthly()
	{
		$interval = new \CF\Chrono\Interval("5th");
		
		$values = [
			1546646400, 1549324800, 1551744000, 1554422400, 
			1557014400, 1559692800, 1562284800, 1564963200, 
			1567641600, 1570233600, 1572912000, 1575504000
		];
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testEndOfMonth()
	{
		$interval = new \CF\Chrono\Interval("eom;@12:00 AM");
		
		$values = [
			1548892800, 1551312000, 1553990400, 1556582400,
			1559260800, 1561852800, 1564531200, 1567209600,
			1569801600, 1572480000, 1575072000, 1577750400
		];
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}

	public function testStarting()
	{
		$interval = new \CF\Chrono\Interval("5th;>1553990400");
		
		$values = [
			1554422400, 
			1557014400, 1559692800, 1562284800, 1564963200, 
			1567641600, 1570233600, 1572912000, 1575504000
		];
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}

	public function testEnding()
	{
		$interval = new \CF\Chrono\Interval("5th;<1564531200");
		
		$values = [
			1546646400, 1549324800, 1551744000, 1554422400, 
			1557014400, 1559692800, 1562284800
		];
		
		$this->assertEquals($values, $interval->getMatchesBetween(START_2019, END_2019));
	}
	
	public function testEveryRequiresValidUnit()
	{
		$this->expectException(\Exception::class);
		$interval = new \CF\Chrono\Interval();
		$interval->every(5, "jiffies");
	}
	
	public function testMinutesDoesNotAllowAt()
	{
		$this->expectException(\Exception::class);
		$interval = new \CF\Chrono\Interval();
		$interval->every(5, "minutes")->at("1:02 PM");
	}
	
	public function testSetException()
	{
		$this->expectException(\Exception::class);
		$interval = new \CF\Chrono\Interval("?");
	}
	
	public function testIntervalOverlap()
	{
		// Timestamp out of range of getMatchesBetween()
		$interval = new \CF\Chrono\Interval();
		$interval->onTimestamp(STAMP_2019);
		$this->assertEquals([], $interval->getMatchesBetween(START_2019, 1551744000));
		
		// Starting past the interval's set end date
		$interval = new \CF\Chrono\Interval();
		$interval->ending(START_2019 - OFFSET_413_AM);
		$this->assertEquals([], $interval->getMatchesBetween(START_2019, END_2019));
		
		// Ending before the interval's set start date
		$interval = new \CF\Chrono\Interval();
		$interval->starting(END_2019 + OFFSET_413_AM);
		$this->assertEquals([], $interval->getMatchesBetween(START_2019, END_2019));
		
		// Starting and ending between valid minute intervals
		$interval = new \CF\Chrono\Interval("~15");
		$this->assertEquals([], $interval->getMatchesBetween(START_2019 + 24, START_2019 + 42));
		
		// Starting and ending between valid daily intervals
		$interval = new \CF\Chrono\Interval("}40");
		$interval->starting(1548806400);
		$this->assertEquals([], $interval->getMatchesBetween(1548892800, 1549324800));
	}
}
