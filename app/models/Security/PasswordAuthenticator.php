<?php
namespace Model\Security;
use Nette;
use Model;
use Nette\Security\AuthenticationException;

class PasswordAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{
	/**
	 * @var Model\Repository\Users
	 */
	protected $users;

	public function __construct(Model\Repository\Users $users)
	{
		$this->users = $users;
	}

	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;
		$password = $this->users->hash($email, $password);

		$user = $this->users->find($email)->fetch();
		if(!$user || $user->password != $password) {
			throw new AuthenticationException("Špatné uživatelské jméno nebo heslo.");
		}

		if(!$user->active) {
			throw new AuthenticationException("Uživatelský účet není aktivní.");
		}

		return new Nette\Security\Identity($user->email, NULL, $user->toArray());
	}
}