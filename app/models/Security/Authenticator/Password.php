<?php
namespace Model\Security\Authenticator;
use Nette, Model, Nette\Security\AuthenticationException;

class Password extends Authenticator
{
	/**
	 * @var Model\Repository\Users
	 */
	protected $users;

	public function __construct(Model\Users $users)
	{
		$this->users = $users;
	}

	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$password = $this->users->hash($username, $password);

		$user = $this->users->find($username);
		if(!$user || $user->password != $password) {
			throw new AuthenticationException("Špatné uživatelské jméno nebo heslo.");
		}

		return $this->getIdentity($user);
	}
}