<?php
namespace Model;
use Nette\Utils\Validators;
use Nette\Utils\Strings;

class Users extends Repository
{
	protected $name = 'users';

	/** static password hashing salt */
	const PASSWORD_SALT = 'ho5Dei3aLi';


	/**
	 * Finds a user by identity
	 * @param string $identity
	 * @return Nette\Database\Selection
	 */
	public function findByIdentity($identity)
	{
		return $this->getTable()
			->where(':identities.identity', $identity);
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

		$data['password'] = $this->hash($data['username'], $data['password']);

		try {
			return $this->create($data);
		}
		catch(\Database\DuplicateEntryException $e) {
			$found = Strings::match($e->getMessage(), "#for key '([^']+)'#");

			if($found && isset($found[1])) {
				if($found[1] == 'PRIMARY') { // username is duplicated
					throw new DuplicateException("Uživatel s tímto uživatelským jménem už existuje.");
				}
				elseif($found[1] == 'email') {
					throw new DuplicateException("Uživatel s tímto emailem už existuje.");
				}
			}

			throw $e;
		}
	}


	/**
	 * Hash a password
	 * @param string $username
	 * @param string $password
	 * @return string
	 */
	public function hash($username, $password)
	{
		return hash('sha512', md5(self::PASSWORD_SALT . $username) . sha1($password . self::PASSWORD_SALT));
	}


	public function changePassword($username, $oldPassword, $newPassword)
	{
		$oldPassword = $this->hash($username, $oldPassword);
		$newPassword = $this->hash($username, $newPassword);

		$user = $this->find($username);
		if($user->password != $oldPassword) {
			throw new Exception("Aktuální heslo není správné.");
		}

		$user->update(array('password' => $newPassword));
	}

	public function changeDetails($username, $data) {
		$user = $this->find($username);
		$user->update($data);
	}
}