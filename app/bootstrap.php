<?php
include __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Config\Configurator;
$configurator->enableDebugger(__DIR__ . '/../logs');
$configurator->setTempDirectory(__DIR__ . '/../tmp');

$configurator->addParameters(array(
	'rootDir' => realpath(__DIR__ . '/../'),
	'wwwDir' => __DIR__ . '/../www'
));

// Enable RobotLoader
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
//	->addDirectory(__DIR__ . '/../www/assets/javascripts/libraries/nette.ajax.js') //TODO: temporary solution
	->register();

/*$configurator->onCompile[] = function ($configurator, $compiler) {
	$compiler->addExtension('ajax', new VojtechDobes\NetteAjax\Extension);
};*/

// Load Configurations
$configurator->addConfig(__DIR__ . '/config/config.neon', FALSE);
$configurator->addConfig(__DIR__ . '/config/config.local.neon', FALSE);

$container = $configurator->createContainer();

// load routes
include __DIR__ . '/router.php';

return $container;
