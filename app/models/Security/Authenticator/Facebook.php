<?php
namespace Model\Security\Authenticator;
use Nette, Model;

class Facebook extends Authenticator
{
	/** @var Model\Repository\Users */
	protected $users;

	public function __construct(Model\Repository\Users $users)
	{
		$this->users = $users;
	}

	public function authenticate(array $credentials)
	{
		$user = $this->users->getTable()
					->where('fbId', $credentials['id'])
					->fetch();

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
				$user = $this->users->register($this->normalizeData($credentials));
				$user->active = true;
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