<?php namespace CF\Chrono;

class Task
{
	private $func;
	private $params;
	private $times;
	
	public function __construct($func, $params = null)
	{
		$this->func = $func;
		$this->params = $params;
	}
	
	public function schedule($times)
	{
		
	}
}