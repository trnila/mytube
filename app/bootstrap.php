<?php
use Nette\Utils\Strings;
include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../vendor/nette/nette/Nette/loader.php';

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
	->register();

// Load Configurations
$configurator->addConfig(__DIR__ . '/config/config.neon', FALSE);
$configurator->addConfig(__DIR__ . '/config/config.local.neon', FALSE);

// Assets router
$container = $configurator->createContainer();
$container->router[] = new Nette\Application\Routers\Route('assets/<file .+\-[a-z0-9]{32}\.[a-z0-9]{2,4}>', function($file) use($container) {
	$hash = Strings::match($file, '/-([a-z0-9]{32})\.[a-z0-9]{2,4}$/');

	// If we dont have hash or there are .., it could be some sort of attack
	if(!isset($hash[1]) || Strings::contains($file, '..')) {
		throw new \Nette\Application\ForbiddenRequestException;
	}

	$name = str_replace("-{$hash[1]}", '', $file);

	// Its file from private space
	if($name[0] === '@') {
		$name = substr($name, 1);
		$realFile = $container->parameters['rootDir'] . $name;

		//TODO: Is it allowed resource?
		throw new Nette\NotImplementedException;
	}
	else {
		$realFile = $container->parameters['wwwDir'] . "/assets/" . $name;
	}

	// Check hash validity
	if(!file_exists($realFile) || md5_file($realFile) !== $hash[1]) {
		throw new \Nette\Application\BadRequestException;
	}

	$httpResponse = $container->httpResponse;
	$httpResponse->setExpiration('+1 year');
	$httpResponse->setHeader('Pragma', 'public');
	$httpResponse->setContentType(Nette\Utils\MimeTypeDetector::fromFile($realFile));

	// If its a javascript file add a source map
	if(Nette\Utils\Strings::endsWith($realFile, '.js')) {
		$httpResponse->addHeader('X-SourceMap', basename($realFile) . '.map');
	}

	readfile($realFile);
});

$container->router[] = new Nette\Application\Routers\Route('<presenter>/<action>', 'Homepage:default');

return $container;