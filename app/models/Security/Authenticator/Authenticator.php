<?php
namespace Model\Security\Authenticator;
use Nette;

abstract class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{
	protected function getIdentity($user)
	{
		if(!$user->active) {
			throw new AuthenticationException("Uživatelský účet není aktivní.");
		}

		return new Nette\Security\Identity($user->id, NULL);
	}
}
