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

// add method to Nette\Database\Connection to disconnect worker from MySQL after period of time to prevent "Mysql server has gone away"
// when mysql is disconnected and tasks queries database, database is reconnected again
Nette\Database\Connection::extensionMethod('disconnect', function($that) {
	$reflection = new ReflectionClass('Nette\Database\Connection');
	$property = $reflection->getProperty('pdo');
	$property->setAccessible(true);
	$property->setValue($that, NULL);
});

$container->addService('Monolog\Logger', createLogger('main'));
$connection = $context->getByType('Nette\Database\Connection');

$log = createLogger('main');
$log->addInfo("Starting worker");


$gmworker= new Worker\MyTubeWorker();
$gmworker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
$gmworker->addServer();

// add job to worker
$processVideo = $context->createInstance('Worker\Job\ProcessVideo');
$context->callInjects($processVideo);
$gmworker->addFunction("processVideo", array($processVideo, 'execute'));

$log->addInfo("Waiting for job...");


// start serving
$lastJob = NULL;
$connection->disconnect();
while(true) {
	@$gmworker->work();
	switch($gmworker->returnCode()) {
		case GEARMAN_SUCCESS:
			$lastJob = new DateTime;
			break;

		case GEARMAN_IO_WAIT:
		case GEARMAN_NO_JOBS:
			sleep(1);


			if($lastJob && $lastJob->diff(new DateTime)->i >= 1) {
				$log->info("Disconnecting from database...");
				$lastJob = NULL;
				$connection->disconnect();
			}

			break;

		default:
			echo $gmworker->returnCode() . "\n";
	}
}