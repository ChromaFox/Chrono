<?php namespace CF\Chrono;

class Interval
{
	private $start = 0;
	private $end = null;
	
	private $timestamp = null;
	
	private $month = null;
	private $day = null;
	
	private $hour = null;
	private $minute = null;
	
	private $endOfMonth = false;
	private $dayOfWeek = null;
	private $interval = null;
	
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
			Also accepts colon-separated 24h time
			
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
		
		if(in_array($char1, ['@', '~','}','*']))
			$intervalParts[0] = intval(substr($intervalParts[0], 1));
		
		// Timestamps are special (and easy)
		if($char1 == '@')
		{
			$this->onTimestamp($intervalParts[0]);
			return;
		}
		else if($char1 == '~')
			$this->every($intervalParts[0], "minutes");
		else if($char1 == '}')
			$this->every($intervalParts[0], "days");
		else if($char1 == '*')
			$this->every($intervalParts[0], "weeks");
		else if($intervalParts[0] == "daily")
			$this->daily();
		// Else it's one of the date-based intervals
		else
			$this->on($intervalParts[0]);
		
		// Remove the first part so we can iterate over the modifiers
		array_shift($intervalParts);
		
		foreach($intervalParts as $modifier)
		{
			$char1 = $modifier[0];
			$modifier = substr($modifier, 1);
			if($char1 == '#')
				$this->every(intval($modifier), $this->dayOfWeek);
			else if($char1 == '@')
				$this->at($modifier);
			else if($char1 == '>')
				$this->starting(intval($modifier));
			else if($char1 == '<')
				$this->ending(intval($modifier));
		}
	}
	
	public function onTimestamp($timestamp)
	{
		$this->type = "timestamp";
		$this->timestamp = $timestamp;
		return $this;
	}
	
	public function daily()
	{
		return $this->every(1, "day");
	}
	
	public function every($interval, $stride)
	{
		if($stride === null)
			return $this;
		
		$days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
		
		$this->interval = $interval;
		if($stride == "minute" || $stride == "minutes")
			$this->type = "minute";
		else if($stride == "day" || $stride == "days")
			$this->type = "day";
		else if($stride == "week" || $stride == "weeks")
		{
			$this->type = "day";
			$this->interval *= 7;
		}
		else if(in_array($stride, $days))
		{
			$this->type = "date";
			$this->dayOfWeek = $stride;
		}
		return $this;
	}
	
	public function on($date)
	{
		$this->type = "date";
		
		$days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
		
		if($date == "eom")
			$this->endOfMonth = true;
		else if(in_array($date, $days))
			$this->dayOfWeek = $date;
		else if(strpos($date, '/') !== false)
		{
			$monthDay = explode('/', $date);
			$this->month = intval($monthDay[0]);
			$this->day = intval($monthDay[1]);
		}
		else
			$this->day = intval($a);
		
		return $this;
	}
	
	public function at($time)
	{
		// Minute/hour based intervals can't be run at a specific time
		if($this->type == "minute")
			return $this;
		
		// Parse the time
		$a = explode(' ', $time);
		$AM = null;
		
		if(isset($a[1]))
			$AM = (strtoupper($a[1]) == 'AM');
		
		$b = explode($a[0]);
		$this->hour = intval($b[0]);
		
		// Go to 24h time if we're not already there
		if($AM !== null)
		{
			// Midnight is special
			if($AM && $this->hour == 12)
				$this->hour = 0;
			// Shift afternoon up
			else if(!$AM && $this->hour > 12)
				$this->hour += 12;
		}
		
		$this->minute = intval($b[1]);
		
		return $this;
	}
	
	public function starting($timestamp)
	{
		$this->start = $timestamp;
		return $this;
	}
	
	public function ending($timestamp)
	{
		$this->end = $timestamp;
		return $this;
	}
}