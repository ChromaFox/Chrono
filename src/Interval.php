<?php namespace CF\Chrono;

class Interval
{
	private $start = 0;
	private $end = null;
	
	private $at = null;
	
	private $month = null;
	private $day = null;
	
	private $hour = null;
	private $minute = null;
	
	private $endOfMonth = false;
	private $dayOfWeek = null;
	private $minutesInterval = null;
	private $daysInterval = null;
	
	private $type = null;
	
	/*
		Accepts a string representing the interval, or null
		String should be of the form:
		
		Root:
			(String must start with these characters)
			@[timestamp]: Run at a specific timestamp. Non-periodic.
			
			~[minutes]: Run every so many minutes starting from the Unix Epoch or the specified timestamp.
			}[days]: Run every so many days starting from the Unix Epoch or the specified timestamp.
			*[weeks]: Run every so many weeks starting from the Unix Epoch or the specified timestamp.
			
			daily: Run the task daily
			[day of week]: Run the task on the day of week
			[day of month]: Run the task on a specific day of the month
			eom: Run the task on the last day of the month, whatever day that may be.
			[date]: Run the task on a specific day of the year, of the form `month/day`
		
		';' Separated Modifiers:
			Each day-of-week task may include a modifier to specify which one of the month to run on, such as first, second, etc.
			It takes the form of `#[1-5]` i.e. "sun;#2" which runs on the second Sunday of the month.
			
			Each day-based task may include a modifier to specify a specific time to run, otherwise default to midnight.
			It takes the form of `@[time]` i.e. "daily;@12:00 PM" which runs at noon instead of midnight.
			
			Each periodic task may include a starting timestamp that it will not process before and non-daily intervals will be processed from.
			It takes the form of `>[timestamp]` i.e. "~30;>1550000000" will run every 30 minutes starting Feb 12th, 2019 at 7:33 PM UTC.
			
			Each periodic task may include an ending timestamp that it will not process after.
			It takes the form of `<[timestamp]` i.e. "~30;<1560000000" will run every 30 minutes from the Unix Epoch until June 8th, 2019 at 1:20 PM UTC.
		
		These modifiers may be combined as long as they are valid.
		i.e. "sun;#3;@4:13 PM;>1550000000;<1560000000" will run 
			at 4:13 PM local time
			every third sunday of the month
			starting Feb 12th, 2019 at 7:33 PM UTC
			until June 8th, 2019 at 1:20 PM UTC
	*/
	
	public function __construct($interval = null)
	{
		$this->set($interval);
	}
	
	public function set($intervalStr)
	{
		// If the user doesn't specify an interval
		// let's default to daily.
		if($intervalStr === null)
		{
			$this->type = "day";
			$this->daysInterval = 1;
			return;
		}
		
		$char1 = $intervalStr[0];
		
		$intervalParts = explode(';', $intervalStr);
		
		// Timestamps are special (and easy)
		if($char1 == '@')
		{
			$this->type = "timestamp";
			$this->at = intval(substr($intervalParts[0], 1));
			return;
		}
		
		$days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
		
		if(in_array($char1, ['~','}','*']))
			$intervalParts[0] = substr($intervalParts[0], 1);
		
		if($char1 == '~')
		{
			$this->type = "minute";
			$this->minutesInterval = intval($intervalParts[0]);
		}
		else if($char1 == '}')
		{
			$this->type = "day";
			$this->daysInterval = intval($intervalParts[0]);
		}
		else if($char1 == '*')
		{
			$this->type = "day";
			$this->daysInterval = intval($intervalParts[0]) * 7;
		}
		else if($intervalParts[0] == "daily")
		{
			$this->type = "day";
			$this->daysInterval = 1;
		}
		else if($intervalParts[0] == "eom")
		{
			$this->type = "date";
			$this->endOfMonth = true;
		}
		else if(in_array($intervalParts[0], $days))
		{
			$this->type = "date";
			$this->dayOfWeek = array_search($intervalParts[0], $days);
		}
		else if(strpos($intervalParts[0], '/') !== false)
		{
			$this->type = "date";
			$monthDay = explode('/', $intervalParts[0]);
			$this->month = $monthDay[0];
			$this->day = $monthDay[1];
		}
		else
		{
			// It's a day of the month I guess?
			$this->type = "date";
			$this->day = intval($intervalParts[0]);
		}
	}
}