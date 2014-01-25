<?php
$context = require __DIR__ . '/../app/bootstrap.php';
Nette\Diagnostics\Debugger::$productionMode = false;

function createLogger($name) {
	$handler = new Monolog\Handler\StreamHandler('php://stdout');
	$handler->setFormatter(new MonologCliFormatter());

	$log = new Monolog\Logger($name);
	$log->pushHandler($handler);

	return $log;
};

$container->addService('Monolog\Logger', createLogger('main'));

$log = createLogger('main');
$log->addInfo("Starting worker");


$gmworker= new Worker\MyTubeWorker();
$gmworker->addServer();

$processVideo = $context->createInstance('Worker\Job\ProcessVideo');
$context->callInjects($processVideo);
$gmworker->addFunction("processVideo", array($processVideo, 'process'));

$log->addInfo("Waiting for job...");
while($gmworker->work())
{
	if ($gmworker->returnCode() != GEARMAN_SUCCESS)
	{
		echo "return_code: " . $gmworker->returnCode() . "\n";
		break;
	}
}