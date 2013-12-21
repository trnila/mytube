<?php
namespace Form;
use Nette;

class Login extends BaseForm
{
	public function __construct(Nette\ComponentModel\IContainer $parent = NULL,  $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->addText('nickname', 'E-mail')
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
			$presenter->user->login($form['nickname']->value, $form['password']->value);

			// save openid if any
			if($presenter->identity) {
				$presenter->context->getByType('Nette\Database\Context')->table('identities')
					->insert(array(
						'user_nickname' => $form['nickname']->value,
						'identity' => $presenter->identity
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
