<?php
namespace Model;
use Nette\Utils\Validators;

class Users extends Repository
{
	/** static password hashing salt */
	const PASSWORD_SALT = 'ho5Dei3aLi';

	/**
	 * Finds a user by email
	 * @param string $email
	 * @return Nette\Database\Selection
	 */
	public function getUserByEmail($email)
	{
		return $this->getTable()
				->where('email', $email);
	}


	/**
	 * Finds a user by identity
	 * @param string $email
	 * @return Nette\Database\Selection
	 */
	public function getUserByIdentity($identity)
	{
		return $this->getTable()
			->where('identities:identity', $identity);
	}

	/**
	 * Register a user
	 * @param array $data
	 */
	public function register(array $data)
	{
		$user = array();

		// Validate
		if(!Validators::isEmail($data['email'])) {
			throw new ModelException('This is not email.');
		}

		$data['password'] = $this->hash($data['email'], $data['password']);

		return $this->getTable()->insert($data);
	}


	/**
	 * Hash a password
	 * @param string $email
	 * @param string $password
	 * @return string
	 */
	public function hash($email, $password)
	{
		return hash('sha512', md5(self::PASSWORD_SALT . $email) . sha1($password . self::PASSWORD_SALT));
	}


	public function changePassword($email, $oldPassword, $newPassword)
	{
		$oldPassword = $this->hash($email, $oldPassword);
		$newPassword = $this->hash($email, $newPassword);

		$user = $this->getUserByEmail($email)->fetch();

		if($user->password != $oldPassword) {
			throw new Exception("Aktuální heslo není správné.");
		}

		$user->update(array('password' => $newPassword));
	}
}