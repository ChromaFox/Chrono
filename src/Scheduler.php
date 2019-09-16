<?php namespace CF\Chrono;

class Scheduler
{
	private $taskList = [];
	private $lockStore = null;
	private $lock = null;
	
	public function __construct()
	{
		$this->lockStore = new Symfony\Component\Lock\Store\SemaphoreStore();
		$factory = new Symfony\Component\Lock\Factory($this->lockStore);
		$this->lock = $factory->createLock("chromafox-tasklock");
	}
	
	public function add(\CF\Chrono\Task $task)
	{
		$this->taskList[] = $task;
	}
	
	public function runTasksSince($lastRun, $currentTime = null)
	{
		if(!$this->lock->acquire())
			return null;
		
		if($currentTime === null)
			$currentTime = time();
		
		try
		{
			foreach($this->taskList as $task)
				$task->run($lastRun, $currentTime);
		}
		finally
		{
			$this->lock->release();
		}
		
		return $currentTime;
	}
}
