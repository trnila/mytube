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
		list($email, $password) = $credentials;
		$password = $this->users->hash($email, $password);

		$user = $this->users->find($email)->fetch();
		if(!$user || $user->password != $password) {
			throw new AuthenticationException("Špatné uživatelské jméno nebo heslo.");
		}

		return $this->getIdentity($user);
	}
}