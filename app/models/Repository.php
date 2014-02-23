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
	 * @param string table name to use
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable($name = NULL)
	{
		return $this->context->table($name ? $name : $this->tableName);
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
	 * @param mixed
	 */
	public function create($data)
	{
		if(is_object($data)) {
			$data = (array) $data;
		}

		$this->getTable()->insert($data);
	}
}