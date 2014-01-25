<?php
$context = require __DIR__ . '/../app/bootstrap.php';

function createLogger($name) {
	$handler = new Monolog\Handler\StreamHandler('php://stdout');
	$handler->setFormatter(new MonologCliFormatter());

	$log = new Monolog\Logger($name);
	$log->pushHandler($handler);

	return $log;
};

$log = createLogger('main');
$log->addInfo("Starting worker");


$gmworker= new GearmanWorker();
$gmworker->addServer();

$ffmpeg = new Task\ffmpeg(createLogger('ffmpeg'));
$gmworker->addFunction("processVideo", array($ffmpeg, 'process'));

$log->addInfo("Waiting for job...");
while($gmworker->work())
{
  if ($gmworker->returnCode() != GEARMAN_SUCCESS)
  {
    echo "return_code: " . $gmworker->returnCode() . "\n";
    break;
  }
}
