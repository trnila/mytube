<?php
namespace Model\Security\Authenticator;
use Nette, Model;

class Facebook extends Authenticator
{
	/** @var Model\Repository\Users */
	protected $users;

	protected $autoRegister = false;

	public function __construct(Model\Users $users)
	{
		$this->users = $users;
	}

	public function setAutoRegister($autoregister = true) {
		$this->autoRegister = (bool) $autoregister;
	}

	public function authenticate(array $credentials)
	{
		$user = $this->users->find(array('fbId' => $credentials['id']));

		if(!$user) {
			$user = $this->users
						->getUserByEmail($credentials['email'])
						->fetch();

			// If user by email exists assign fbId if not already exists
			if($user) {
				if(empty($user->fbId)) {
					$user->update(array('fbId' => $credentials['id']));
				}
				else {
					throw new Exception("Already exists for another facebook application id");
				}
			}
			else {
				if($this->autoRegister) {
					$user = $this->users->register($this->normalizeData($credentials));
					$user->active = true;
				}
				else {
					throw new RegisterException();
				}
			}
		}

		return $this->getIdentity($user);
	}

	protected function normalizeData(array $data)
	{
		$user = array();
		$user['email'] = $data['email'];
		//$user['username'] = $data['username'];
		$user['fbId'] = $data['id'];
		return $user;
	}
}


class RegisterException extends Nette\Security\AuthenticationException
{
}