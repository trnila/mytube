<?php
$container = include __DIR__ . '/../app/bootstrap.php';
Nette\Diagnostics\Debugger::$productionMode = FALSE;

$versionFile = __DIR__ . '/.version';
$actualVersion = (int) (@file_get_contents($versionFile));

echo "\e[0;32mActual version is {$actualVersion}\e[0;0m\n";

$migrations = array();
foreach(Nette\Utils\Finder::findFiles('*.sql')->from(__DIR__ . '/migrations') as $file) {
	$version = Nette\Utils\Strings::match($file->getFileName(), '/^(\d+)_/');

	if(!isset($version[1])) {
		continue;
	}

	$version = (int) $version[1];
	if($version <= $actualVersion) {
		continue;
	}

	$migrations[$version] = $file;
}

// Sort by versions
ksort($migrations);

foreach($migrations as $version => $file) {
	echo "\e[0;32mMigrating to {$version}\e[0;0m\n";

	$sql = file_get_contents($file);
	foreach(preg_split("/;\s*\n/", $sql) as $query) {
		$query = trim($query);
		if(empty($query)) {
			continue;
		}

		echo $query . "\n";
		try {
			$container->database->query($query);
		}
		catch(PDOException $e) {
			echo "\e[0;31m" . $e->getMessage() . "\e[0;0m\n";
			Nette\Diagnostics\Debugger::log($e);
		}
	}

	file_put_contents($versionFile, $version);
}
