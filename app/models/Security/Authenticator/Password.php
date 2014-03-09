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

		$user = $this->users->findBy(array('username' => $username))->fetch();
		if(!$user || !Nette\Security\Passwords::verify($password, $user->password)) {
			throw new AuthenticationException("Špatné uživatelské jméno nebo heslo.");
		} elseif(Nette\Security\Passwords::needsRehash($user->password)) {
			$this->users->update($user->id, array(
				'password' => Nette\Utils\Passwords::hash($password)
			));
		}

		return $this->getIdentity($user);
	}
}