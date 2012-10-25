<?php
namespace Model\Security;
use Nette;

class Authorizator extends Nette\Security\Permission
{
	public function __construct(Nette\Security\User $user)
	{
		$this->addRole('authenticated');

		// Resources
		$this->addResource('video');

		$this->allow('authenticated', 'video', 'edit', function($acl) use($user) {
			return $acl->queriedResource->user_id == $user->id;
		});

		$this->allow('authenticated', 'video', 'show');
	}
}