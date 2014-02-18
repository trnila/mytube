<?php
namespace Model;
use Nette;

class Repository extends Nette\Object
{
	/**
	 * @var Nette\Database\Context
	*/
	protected $context;

	public function __construct(Nette\Database\Context $context)
	{
		$this->context = $context;
	}

	/**
	 * Returns all rows from table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->context->table($this->tableName);
	}

	/**
	 * Returns all rows from table, alias for getTable
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->getTable();
	}

	/**
	 * @param  mixed $primary
	 * @return \Nette\Database\Table\Selection
	 */
	public function find($primary)
	{
		return $this->getTable()->wherePrimary($primary)->fetch();
	}

	/**
	 * @param  array $by conditions
	 * @return \Nette\Database\Table\Selection
	 */
	public function findBy(array $by)
	{
		return $this->getTable()->where($by);
	}


	/**
	 * @param array $by conditions
	 * @return \Nette\Database\Table\Selection
	 */
	public function findOneBy(array $by)
	{
		return $this->findBy($by)->limit(1)->fetch();
	}

	/**
	 * @param array
	 */
	public function create(array $data)
	{
		$this->getTable()->insert($data);
	}
}