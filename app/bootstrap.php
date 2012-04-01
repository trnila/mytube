<?php
include __DIR__ . '/../vendor/.composer/autoload.php';
include __DIR__ . '/../vendor/nette/nette/Nette/loader.php';

$configurator = new Nette\Config\Configurator;
$configurator->setProductionMode(FALSE);
$configurator->enableDebugger(__DIR__ . '/../logs');
$configurator->setTempDirectory(__DIR__ . '/../tmp');

// TODO: use own configurator instead of this
$configurator->addParameters(array('wwwDir' => __DIR__ . '/../www'));

// Enable RobotLoader
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	//->addDirectory(__DIR__ . '/../libs/')
	->register();

// Model repository
$configurator->onCompile[] = function ($configurator, $compiler) {
	$compiler->addExtension('repository', new Model\Repository\Compiler);
};

// Load Configurations
$configurator->addConfig(__DIR__ . '/config/config.neon', FALSE);
$configurator->addConfig(__DIR__ . '/config/config.local.neon', FALSE);
return $configurator->createContainer();