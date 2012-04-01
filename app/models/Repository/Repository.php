<?php
namespace Model\Repository;
use Nette;

class Repository extends Nette\Object
{
	/** @var Nette\Database\Connection */
	protected $connection;

	public function __construct(Nette\Database\Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable()
	{
		$name = substr($this->reflection->name, 17);
		$name = lcfirst($name);
		return $this->connection->table($name);
	}
}