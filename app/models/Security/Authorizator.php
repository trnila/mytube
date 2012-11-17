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
		$this->addResource('comment');

		$this->allow('authenticated', 'video', 'edit', function($acl) use($user) {
			return $acl->queriedResource->user_nickname == $user->id;
		});

		$this->allow('authenticated', 'comment', 'delete', function($acl) use($user) {
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
	}
}