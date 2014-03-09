<?php
namespace Form;
use Nette;
use Model;

class Login extends BaseForm
{
	/**
	 * @var Model\Users
	 * @inject
	*/
	public $users;

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL,  $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->addText('username', 'E-mail')
			->setRequired();

		$this->addPassword('password', 'Heslo')
			->setRequired();

		$this->addPrimary('login', 'Přihlásit se');
		$this->onSuccess[] = callback($this, 'login');
	}

	public function login($form)
	{
		try {
			$presenter = $this->presenter;
			$presenter->user->login($form['username']->value, $form['password']->value);

			$persistentLogin = $this->presenter->getPersistentLogin();
			if(isset($persistentLogin[$presenter->user->identity->email])) {
				$this->users->addIdentity($presenter->user->id, array(
					'type' => 'openid',
					'identity' => $persistentLogin[$presenter->user->identity->email]
				));
			}


			$presenter->flashMessage('Byl jste přihlášen.', $presenter::FLASH_SUCCESS);
			$presenter->redirectHome();
		}
		catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}
}

interface ILoginFactory {
	/**
	 * @return Login
	*/
	public function create();
}