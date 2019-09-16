<?php namespace CF\Chrono;

class Task
{
	private $func;
	private $params;
	private $times;
	private $noDuplicates;
	
	public function __construct($func, $params = null)
	{
		$this->func = $func;
		$this->params = $params;
		$this->times = [];
		$this->noDuplicates = true;
	}
	
	public function allowDuplicateRuns()
	{
		$this->noDuplicates = false;
	}
	
	public function schedule($intervals)
	{
		if(gettype($intervals) == "array")
			$this->times = array_merge(this->times, $intervals);
		else
			$this->times[] = $intervals;
	}
	
	public function run($start, $end)
	{
		$timestamps = [];
		
		foreach($this->times as $interval)
			$timestamps = array_merge($timestamps, $interval->getMatchesBetween($start, $end));
		
		if($this->noDuplicates)
			$timestamps = array_unique($timestamps);
		
		$timestamps = sort($timestamps);
		
		end($timestamps);
		$end = key($timestamps);
		reset($timestamps);
		
		foreach($timestamps as $i => $timestamp)
			call_user_func(this->func, $timestamp, $i == $end, $this->params);
	}
}