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
		$this->addText('email', 'E-mail')
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
			$presenter->user->login($form['email']->value, $form['password']->value);

			$user = $this->users->findByEmail($form['email']->value);

			$identity = $this->presenter->getPairedIdentity($form['email']->value);
			if($identity) {
				$this->users->addIdentity($user->id, array(
					'type' => $identity['type'],
					'identity' => $identity['identity']
				));
			}

			$presenter->clearPersistents();
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