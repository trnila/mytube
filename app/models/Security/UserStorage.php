<?php
namespace Model\Security;
use Nette;
use Nette\Security\Identity;
use Nette\Security\IIdentity;


class UserStorage extends Nette\Http\UserStorage
{
	protected $database;
	protected $httpRequest;

	protected $identity;
	protected $isAuthenticated = FALSE;

	public function __construct(Nette\Http\Session $sessionHandler, Nette\Database\Context $database, Nette\Http\Request $httpRequest)
	{
		parent::__construct($sessionHandler);
		$this->database = $database;
		$this->httpRequest = $httpRequest;

		$session = $this->getSessionSection(FALSE);
		if(!$session || !$session->identity) {
			$this->identity = NULL;
		}
		else {
			$user = $this->database->table('users')
				->select('*')
				->wherePrimary($session->identity->id)
				->fetch();

			if(!$user) {
				$this->identity = NULL;
			}
			else {
				$roles = array('user');

				if($user->admin) {
					$roles[] = 'admin';
				}

				$this->identity = new Identity($user->id, $roles, iterator_to_array($user));
			}
		}
	}

	public function getIdentity()
	{
		return $this->identity;
	}
}