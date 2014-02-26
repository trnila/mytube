<?php
namespace Model;
use Nette;
use PDOException;

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
		try {
			if(is_object($data)) {
				$data = (array) $data;
			}

			return $this->getTable()->insert($data);
		} catch(PDOException $e) {
			if(is_array($e->errorInfo) && $e->errorInfo[1] == 1062) {
				$exception = new DuplicateException($e->getMessage(), $e->errorInfo[1], $e);

				$parts = Nette\Utils\Strings::match($e->errorInfo[2], "/key '([^']+)'/");
				if(isset($parts[1])) {
					$exception->setKey($parts[1]);
				}

				throw $exception;
			} else {
				throw $e;
			}
		}
	}

	public function delete($id)
	{
		$this->getTable()
			->wherePrimary($id)
			->delete();
	}
}