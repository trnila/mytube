<?php
namespace Model\Entity;
use Nette;

class User extends Nette\Object implements Nette\Security\IResource
{

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $username;

	/**
	 * @var string
	 */
	public $firstname;

	/**
	 * @var string
	 */
	public $lastname;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * @var string
	 */
	public $password;

	/**
	 * @var string
	 */
	public $aboutme;

	/**
	 * @var bool
	 */
	public $active;

	/**
	 * Creates new instance from a row
	 * @return Model\Entity\User
	 */
	public static function create($row)
	{
		$user = new static;
		foreach(array('id', 'username', 'firstname', 'lastname', 'email', 'password', 'active', 'aboutme') as $column) {
			$user->{$column} = $row[$column];
		}

		return $user;
	}

	/**
	 * Returns full name of user
	 * @return string
	 */
	public function getFullName()
	{
		return trim($this->firstname . ' ' . $this->lastname);
	}

	public function getResourceId()
	{
		return 'user';
	}
}