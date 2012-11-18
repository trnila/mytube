<?php
namespace Model\Security;
use Nette;

class Authorizator extends Nette\Security\Permission
{
	public function __construct(Nette\Security\User $user)
	{
		$this->addRole('guest');
		$this->addRole('user');
		$this->addRole('admin');

		// Resources
		$this->addResource('video');
		$this->addResource('comment');

		$this->allow('user', 'video', 'edit', function($acl) use($user) {
			return $acl->queriedResource->user_nickname == $user->id;
		});

		$this->allow('user', 'comment', 'delete', function($acl) use($user) {
			return $acl->queriedResource->user_nickname == $user->id;
		});

		$this->allow($this::ALL, 'video', 'show', function($acl) use($user) {
			$video = $acl->queriedResource;

			if(!$video->processed) {
				return false;
			}

			if($video->enabled) {
				return true;
			}

			if($video->user_nickname == $user->id) {
				return true;
			}

			return false;
		});
		
		$this->addResource('user');

		$this->allow('admin', 'user', $this::ALL);
		$this->deny('admin', 'user', 'delete', function($acl) use($user) {
			return $acl->queriedResource->nickname == $user->id;
		});

		$this->deny('admin', 'user', 'activation', function($acl) use($user) {
			return $acl->queriedResource->nickname == $user->id;
		});
	}
}