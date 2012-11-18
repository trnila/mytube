<?php
namespace Model\Security;
use Nette;

class Authorizator extends \Nette\Security\Permission
{
	public function __construct(Nette\Security\User $user)
	{
		$this->addRole('user');
		$this->addRole('admin');
		
		$this->addResource('user');

		$this->allow('admin', 'user', $this::ALL);
		$this->deny('admin', 'user', 'delete', function($acl) use($user) {
			return $acl->queriedResource->nickname == $user->id;
		});
	}
}