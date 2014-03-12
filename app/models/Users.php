<?php
namespace Model;
use Nette\Utils\Validators;
use Nette\Utils\Strings;
use Nette;

class Users extends Repository
{
	protected $tableName = 'users';

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
	 * @return Model\Entity\User
	 */
	public function find($id)
	{
		$row = $this->getTable()->wherePrimary($id)->fetch();
		return $row ? Entity\User::create($row) : NULL;
	}

	/**
	 * @param $user Entity\User
	 * @param $data array
	 */
	public function update(Entity\User $user, array $data)
	{
		parent::find($user->id)->update($data);
	}

	/**
	 * Register a user
	 * @param array $data
	 */
	public function register(array $data)
	{
		// Validate
		if(!Validators::isEmail($data['email'])) {
			throw new ModelException('This is not email.');
		}

		$data['password'] = Nette\Security\Passwords::hash($data['password']);

		try {
			$row = array();
			foreach(array('username', 'firstname', 'lastname', 'email', 'password') as $item) {
				$row[$item] = isset($data[$item]) ? $data[$item] : NULL;
			}

			$user = $this->create($row);

			if(isset($data['fbId'])) {
				$this->addIdentity($user->id, array(
					'type' => 'facebook',
					'identity' => $data['fbId']
				));
			}

			return $user;
		}
		catch(DuplicateEntryException $e) {
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

	public function addIdentity($user_id, $data)
	{
		$this->getTable('users_identities')
			->insert(array('user_id' => $user_id) + $data);
	}

	public function changePassword($username, $oldPassword, $newPassword)
	{
		$user = $this->find($username);
		if(!Nette\Security\Passwords::verify($oldPassword, $user->password)) {
			throw new Exception("Aktuální heslo není správné.");
		}

		$this->update($user, array(
			'password' => Nette\Security\Passwords::hash($newPassword)
		));
	}

	public function changeDetails($username, $data) {
		$user = $this->find($username);
		$user->update($data);
	}

	public function changeAvatar($user_id, Nette\Image $image = NULL)
	{
		$user = $this->find($user_id);

		if($image) {
			$hash = Nette\Utils\Strings::random();
			$image->resize(160, 160)->save(Entity\User::formatAvatarLocation($user->id, $hash));

			// set new avatar
			$this->update($user, array('avatar' => $hash));
		} else {
			$this->update($user, array('avatar' => NULL));
		}

		// delete old hash if exists
		if($user->avatar) {
			@unlink(Entity\User::formatAvatarLocation($user->id, $user->avatar));
		}
	}
}