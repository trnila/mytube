<?php
$container = include __DIR__ . '/../app/bootstrap.php';
Nette\Diagnostics\Debugger::$productionMode = FALSE;
$database = $container->database;

class Migrate extends Nette\Object {
	protected $database;

	protected $migrations = array();

	public function __construct(Nette\Database\Connection $database) {
		$this->database = $database;

		$this->createMigrationTable();
		$this->obtainMigrations();
	}

	public function migrate() {
		$migrations = array();

		foreach(Nette\Utils\Finder::findFiles('*.sql', '*.php')->from(__DIR__ . '/migrations') as $file) {
			list($migration) = explode('_', $file->getFileName(), 2);

			if(!in_array($migration, $this->migrations)) {
				$migrations[$migration] = $file;
			}
		}

		ksort($migrations);

		foreach($migrations as $version => $migration) {
			echo "\e[0;32mMigrating {$migration->getFileName()}\e[0;0m\n";

			$pathinfo = pathinfo($migration->getBaseName());
			switch ($pathinfo['extension']) {
				case 'sql': {
					$this->migrateSql($migration);
					break;
				}
				default:
					throw new Exception("Unknown migration");
			}

			$this->database->query('INSERT INTO schema_migrations(migration) VALUES(?)', $version);
		}
	}

	protected function createMigrationTable() {
		$this->database->query("CREATE TABLE IF NOT EXISTS`schema_migrations` (
  								 `migration` int NOT NULL,
  								 PRIMARY KEY(migration)
								) COMMENT='' ENGINE='InnoDB'");
	}

	protected function obtainMigrations() {
		$migrations = $this->database->query('SELECT * FROM schema_migrations');
		foreach($migrations as $migration) {
			$this->migrations[] = $migration->migration;
		}
	}

	protected function migrateSql($file) {
		$sql = file_get_contents($file);
		foreach(preg_split("/;\s*\n/", $sql) as $query) {
			$query = trim($query);
			if(empty($query)) {
				continue;
			}

			echo $query . "\n";
			try {
				$this->database->query($query);
			}
			catch(PDOException $e) {
				echo "\e[0;31m" . $e->getMessage() . "\e[0;0m\n";
				exit;
				Nette\Diagnostics\Debugger::log($e);
			}
		}
	}


}

$m = new Migrate($database);
$m->migrate();