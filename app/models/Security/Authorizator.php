<?php
namespace Model\Security;
use Nette;

class Authorizator extends \Nette\Security\Permission
{
	public function __construct(Nette\Security\User $user)
	{
		// Add roles
		$this->addRole('guest');
		$this->addRole('authenticated');
		$this->addRole('user');
		$this->addRole('admin');
		
		// Add resources
		$this->addResource('user');

		// Call all setup methods
		foreach($this->reflection->methods as $method) {
			if(Nette\Utils\Strings::startsWith($method->name, 'permit')) {
				$this->{$method->name}($user);
			}
		}
	}

	/**
	 * All admins could do everything with user, but they can't deactivate or delete themselves 
	 */
	protected function permitUser($user)
	{
		$this->allow('admin', 'user', $this::ALL);
		$this->deny('admin', 'user', 'delete', function($acl) use($user) {
			return $acl->queriedResource->nickname == $user->id;
		});

		$this->deny('admin', 'user', 'activation', function($acl) use($user) {
			return $acl->queriedResource->nickname == $user->id;
		});

		$this->deny('admin', 'user', 'edit', function($acl) use($user) {
			return $acl->queriedResource->nickname == $user->id;
		});
	}
}