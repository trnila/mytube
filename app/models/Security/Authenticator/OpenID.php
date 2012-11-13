<?php
namespace Model\Security\Authenticator;
use Nette, Model;

class OpenID extends Authenticator
{
	/** @var Model\Repository\Users */
	protected $users;

	public function __construct(Model\Users $users)
	{
		$this->users = $users;
	}

	public function authenticate(array $credentials)
	{
		list($identity, $attributes) = $credentials;
		$user = $this->users->findByIdentity($identity)->fetch();

		if($user) {
			return $this->getIdentity($user);
		}

		if($this->users->find(array('email' => $attributes['contact/email']))) {
			throw new NeedLoginException;
		}

		throw new RegisterException;
	}
}

class NeedLoginException extends Nette\Security\AuthenticationException {}