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
	 * @var bool
	 */
	public $admin;

	/**
	 * @var string
	*/
	public $avatar;


	/**
	 * Creates new instance from a row
	 * @return Model\Entity\User
	 */
	public static function create($row)
	{
		$user = new static;
		foreach(array('id', 'username', 'firstname', 'lastname', 'email', 'password', 'active', 'aboutme', 'admin', 'avatar') as $column) {
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

	public function getAvatarLocation()
	{
		if($this->avatar) {
			return self::formatAvatarLocation($this->id, $this->avatar);
		}

		return NULL;
	}

	public function getResourceId()
	{
		return 'user';
	}


	public static function formatAvatarLocation($user_id, $avatar)
	{
		return "avatars/{$user_id}-{$avatar}.png";
	}
}