<?php
namespace Model\Security;
use Nette;

class Authorizator extends Nette\Security\Permission
{
	public function __construct(Nette\Security\User $user)
	{
		// Add roles
		$this->addRole('guest');
		$this->addRole('authenticated');
		$this->addRole('user');
		$this->addRole('admin');

		// Resources
		$this->addResource('video');
		$this->addResource('comment');
		$this->addResource('playlist');

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
			return $acl->queriedResource->id == $user->id;
		});

		$this->deny('admin', 'user', 'activation', function($acl) use($user) {
			return $acl->queriedResource->id == $user->id;
		});

		$this->deny('admin', 'user', 'edit', function($acl) use($user) {
			return $acl->queriedResource->id == $user->id;
		});

		$this->allow('user', 'user', 'edit', function($acl) use($user) {
			return $acl->queriedResource->id == $user->id;
		});
	}

	protected function permitVideo($user)
	{
		$this->allow('user', 'video', array('edit', 'delete'), function($acl) use($user) {
			return $acl->queriedResource->user_id == $user->id;
		});

		$this->allow($this::ALL, 'video', 'show', function($acl) use($user) {
			$video = $acl->queriedResource;

			// TODO: fix
			/*if(!$video->processed && $acl->queriedResource->user_id != $user->id) {
				return false;
			}*/

			if($video->enabled) {
				return true;
			}

			if($video->user_id == $user->id) {
				return true;
			}

			return false;
		});

		$this->allow('admin', 'video', $this::ALL);
	}

	protected function permitComments($user)
	{
		$this->allow('user', 'comment', 'delete', function($acl) use($user) {
			return $acl->queriedResource->user_id == $user->id;
		});

		$this->allow('admin', 'comment', $this::ALL);
	}

	protected function permitPlaylist($user)
	{
		$this->allow($this::ALL, 'playlist', array('manage', 'delete'), function($acl) use($user) {
			return $acl->queriedResource->user_id == $user->id;
		});

		$this->allow($this::ALL, 'playlist', 'show', function($acl) use ($user) {
			return $acl->queriedResource->private == FALSE || $acl->queriedResource->user_id = $user->id;
		});
	}
}