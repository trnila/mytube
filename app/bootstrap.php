<?php
include __DIR__ . '/../libs/Nette/Nette/loader.php';

$configurator = new Nette\Config\Configurator;
$configurator->setProductionMode(FALSE);
$configurator->enableDebugger(__DIR__ . '/../logs');
$configurator->setTempDirectory(__DIR__ . '/../tmp');

// TODO: use own configurator instead of this
$configurator->addParameters(array('wwwDir' => __DIR__ . '/../www'));

// Enable RobotLoader
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs/')
	->register();

// Load Configurations
$configurator->addConfig(__DIR__ . '/config/config.neon', FALSE);
$configurator->addConfig(__DIR__ . '/config/config.local.neon', FALSE);
$container = $configurator->createContainer();