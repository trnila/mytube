<?php
namespace Task;
use Monolog;

abstract class Task
{
	protected $logger;

	public function __construct(Monolog\Logger $logger)
	{
		$this->logger = $logger;
	}

	abstract public function process(\GearmanJob $job);
}