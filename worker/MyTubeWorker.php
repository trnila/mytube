<?php
namespace Worker;
use GearmanWorker;
use Nette;

class MyTubeWorker extends GearmanWorker
{
	public function addFunction($function_name, $function, $data = NULL, $timeout = NULL)
	{
		return parent::addFunction($function_name, function() use($function) {
			try {
				call_user_func_array($function, func_get_args());
			} catch(\Exception $e) {
				$result = GEARMAN_WORK_EXCEPTION;
				echo "Gearman: CAUGHT EXCEPTION: " . $e->getMessage();

				Nette\Diagnostics\Debugger::log($e);
			}
		}, $data, $timeout);
	}
}