<?php
namespace Worker\Job;
use Monolog;

abstract class Job
{
	/**
	 * @var Monolog\Logger
	 * @inject
	*/
	public $logger;

	abstract public function execute(\GearmanJob $job);
}